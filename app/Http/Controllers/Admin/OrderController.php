<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\Order;
use App\Models\User;
use App\Models\Driver;
use App\Models\Service;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    /**
     * Display a listing of orders
     */
    public function index(Request $request)
    {
        try {
            $query = Order::with([
                'user:id,name,phone,email',
                'address',
                'orderProducts.product'
            ])->orderBy('created_at', 'desc');

            // Filter by order status
            if ($request->filled('order_status')) {
                $query->where('order_status', $request->order_status);
            }

            // Filter by payment status
            if ($request->filled('payment_status')) {
                $query->where('payment_status', $request->payment_status);
            }

            // Filter by payment type
            if ($request->filled('payment_type')) {
                $query->where('payment_type', $request->payment_type);
            }

            // Filter by date range
            if ($request->filled('from_date')) {
                $query->whereDate('date', '>=', $request->from_date);
            }

            if ($request->filled('to_date')) {
                $query->whereDate('date', '<=', $request->to_date);
            }

            // Search by order number or user name
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('number', 'like', "%{$search}%")
                      ->orWhereHas('user', function($userQuery) use ($search) {
                          $userQuery->where('name', 'like', "%{$search}%")
                                   ->orWhere('phone', 'like', "%{$search}%");
                      });
                });
            }

            $orders = $query->paginate(15);

            // Add status labels and statistics
            $orders->getCollection()->transform(function ($order) {
                $order->order_status_label = $this->getOrderStatusLabel($order->order_status);
                $order->payment_status_label = $this->getPaymentStatusLabel($order->payment_status);
                $order->items_count = $order->orderProducts->sum('quantity');
                return $order;
            });

            // Get statistics
            $statistics = $this->getOrderStatistics();

            return view('admin.orders.index', compact('orders', 'statistics'));

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to load orders: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified order
     */
    public function show($id)
    {
        try {
            $order = Order::with([
                'user:id,name,phone,email,country_code',
                'address',
                'orderProducts.product'
            ])->findOrFail($id);

            $order->order_status_label = $this->getOrderStatusLabel($order->order_status);
            $order->payment_status_label = $this->getPaymentStatusLabel($order->payment_status);

            return view('admin.orders.show', compact('order'));

        } catch (\Exception $e) {
            return back()->with('error', 'Order not found: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified order
     */
    public function edit($id)
    {
        try {
            $order = Order::with([
                'user:id,name,phone,email',
                'address',
                'orderProducts.product'
            ])->findOrFail($id);

            $users = User::where('activate', 1)->get(['id', 'name', 'phone', 'email']);
            
            return view('admin.orders.edit', compact('order', 'users'));

        } catch (\Exception $e) {
            return back()->with('error', 'Order not found: ' . $e->getMessage());
        }
    }


    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'order_status' => 'required|integer|in:1,2,3,4,5,6',
                'payment_status' => 'required|integer|in:1,2',
                'note' => 'nullable|string|max:500',
            ]);

            $order = Order::with('orderProducts')->findOrFail($id);
            $oldStatus = $order->order_status;
            $oldPaymentStatus = $order->payment_status;

            DB::beginTransaction();

            try {
                // Update order
                $order->update([
                    'order_status' => $request->order_status,
                    'payment_status' => $request->payment_status,
                    'note' => $request->note
                ]);

                // Handle payment status change
                if ($oldPaymentStatus != $request->payment_status) {
                    $this->handlePaymentStatusChange($order, $request->payment_status);
                }

                // Handle delivery - create voucher when status changes to 4 (Delivered)
                if ($request->order_status == 4 && $oldStatus != 4) {
                    $warehouseId = $request->warehouse_id ?? $this->getDefaultWarehouseId();
                    $this->createDeliveryVoucher($order, $warehouseId);
                }

                // Handle refund if status changed to refund
                if ($request->order_status == 6 && $oldStatus != 6) {
                    $this->processRefund($order);
                }

                DB::commit();

                $message = 'Order updated successfully';
                if ($request->order_status == 4 && $oldStatus != 4) {
                    $message .= ' and delivery voucher created.';
                }

                return redirect()->route('orders.show', $order->id)
                    ->with('success', $message);

            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update order: ' . $e->getMessage());
        }
    }

    /**
     * Get default warehouse ID
     */
    private function getDefaultWarehouseId()
    {
        $defaultWarehouse = DB::table('warehouses')
            ->orderBy('id', 'asc')
            ->first();
        
        if (!$defaultWarehouse) {
            throw new \Exception('No warehouse found. Please create a warehouse first.');
        }
        
        return $defaultWarehouse->id;
    }

    /**
     * Create delivery voucher when order is delivered
     */
    private function createDeliveryVoucher(Order $order, $warehouseId)
    {
        try {
            // Validate warehouse exists
            $warehouse = DB::table('warehouses')->find($warehouseId);
            if (!$warehouse) {
                throw new \Exception('Selected warehouse not found.');
            }

            // Check if voucher already exists for this order
            $existingVoucher = DB::table('note_vouchers')
                ->where('order_id', $order->id)
                ->where('type', 2)
                ->first();

            if ($existingVoucher) {
                \Log::warning("Delivery voucher already exists for order {$order->id}");
                return; // Don't create duplicate voucher
            }

            // Generate voucher number
            $voucherNumber = $this->generateVoucherNumber(2); // type 2 = out

            // Create note voucher (type 2 = out, meaning products going out of warehouse)
            $noteVoucher = DB::table('note_vouchers')->insertGetId([
                'number' => $voucherNumber,
                'type' => 2, // 2 = out (products leaving warehouse for delivery)
                'date_note_voucher' => now()->toDateString(),
                'note' => "Delivery voucher for Order #{$order->number} - Order delivered to customer",
                'warehouse_id' => $warehouseId,
                'order_id' => $order->id,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Get order products
            $orderProducts = DB::table('order_products')
                ->where('order_id', $order->id)
                ->get();

            if ($orderProducts->isEmpty()) {
                throw new \Exception('No products found in this order.');
            }

            // Create voucher products entries
            $voucherProductsData = [];
            foreach ($orderProducts as $orderProduct) {
                $voucherProductsData[] = [
                    'quantity' => $orderProduct->quantity,
                    'note' => "Delivered - Order #{$order->number}",
                    'product_id' => $orderProduct->product_id,
                    'note_voucher_id' => $noteVoucher,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }

            // Bulk insert voucher products
            DB::table('voucher_products')->insert($voucherProductsData);

            // Log the voucher creation
            \Log::info("Delivery voucher created for order {$order->id}", [
                'order_id' => $order->id,
                'voucher_id' => $noteVoucher,
                'voucher_number' => $voucherNumber,
                'warehouse_id' => $warehouseId,
                'products_count' => $orderProducts->count()
            ]);

        } catch (\Exception $e) {
            \Log::error("Failed to create delivery voucher for order {$order->id}: " . $e->getMessage());
            throw new \Exception("Failed to create delivery voucher: " . $e->getMessage());
        }
    }

    /**
     * Generate unique voucher number
     */
    private function generateVoucherNumber($type)
    {
        $lastVoucher = DB::table('note_vouchers')
            ->where('type', $type)
            ->lockForUpdate() // Prevent race conditions
            ->orderBy('number', 'desc')
            ->first();
        
        return $lastVoucher ? $lastVoucher->number + 1 : 1;
    }

  

    /**
     * Get order statistics
     */
    private function getOrderStatistics()
    {
        return [
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('order_status', 1)->count(),
            'delivered_orders' => Order::where('order_status', 4)->count(),
            'canceled_orders' => Order::where('order_status', 5)->count(),
            'total_revenue' => Order::where('order_status', 4)->sum('total_prices'),
            'unpaid_orders' => Order::where('payment_status', 2)->count(),
            'today_orders' => Order::whereDate('created_at', today())->count(),
            'this_month_orders' => Order::whereMonth('created_at', now()->month)
                                       ->whereYear('created_at', now()->year)
                                       ->count()
        ];
    }

    /**
     * Handle payment status change
     */
    private function handlePaymentStatusChange($order, $newPaymentStatus)
    {
        if ($newPaymentStatus == 1 && $order->payment_type == 'wallet') {
            // Deduct from user wallet if payment is marked as paid
            $user = $order->user;
            if ($user->balance >= $order->total_prices) {
                $user->decrement('balance', $order->total_prices);
                
                // Create wallet transaction
                WalletTransaction::create([
                    'user_id' => $user->id,
                    'amount' => $order->total_prices,
                    'type_of_transaction' => 2, // withdrawal
                    'note' => "Payment for order #{$order->number}"
                ]);
            }
        }
    }

    /**
     * Process refund
     */
    private function processRefund($order)
    {
        if ($order->payment_status == 1) { // Only refund if paid
            $user = $order->user;
            
            // Add refund to user wallet
            $user->increment('balance', $order->total_prices);
            
            // Create wallet transaction for refund
            WalletTransaction::create([
                'user_id' => $user->id,
                'amount' => $order->total_prices,
                'type_of_transaction' => 1, // add
                'note' => "Refund for order #{$order->number}"
            ]);
        }
    }

    /**
     * Get order status label
     */
    private function getOrderStatusLabel($status)
    {
        $labels = [
            1 => 'Pending',
            2 => 'Accepted', 
            3 => 'On The Way',
            4 => 'Delivered',
            5 => 'Canceled',
            6 => 'Refund'
        ];

        return $labels[$status] ?? 'Unknown';
    }

    /**
     * Get payment status label
     */
    private function getPaymentStatusLabel($status)
    {
        $labels = [
            1 => 'Paid',
            2 => 'Unpaid'
        ];

        return $labels[$status] ?? 'Unknown';
    }

  

   
}