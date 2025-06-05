<?php
namespace App\Http\Controllers\Api\v1\User;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Service;
use App\Models\User;
use App\Traits\Responses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    use Responses;

    /**
     * Display a listing of the user's orders
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $validator = Validator::make($request->all(), [
            'status' => 'sometimes|in:1,2,3,4,5,6,7',
            'payment_status' => 'sometimes|in:1,2',
            'payment_method' => 'sometimes|in:1,2,3',
            'per_page' => 'sometimes|integer|min:5|max:100',
            'sort_by' => 'sometimes|in:date,price',
            'sort_direction' => 'sometimes|in:asc,desc',
            'from_date' => 'sometimes|date_format:Y-m-d',
            'to_date' => 'sometimes|date_format:Y-m-d|after_or_equal:from_date'
        ]);
        
        if ($validator->fails()) {
            return $this->error_response('Validation error', $validator->errors());
        }
        
        $query = Order::where('user_id', $user->id);
        
        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by payment status if provided
        if ($request->has('payment_status')) {
            $query->where('status_payment', $request->payment_status);
        }
        
        // Filter by payment method if provided
        if ($request->has('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }
        
        // Filter by date range if provided
        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        
        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }
        
        // Apply sorting
        $sortBy = $request->sort_by ?? 'created_at';
        $sortDirection = $request->sort_direction ?? 'desc';
        
        if ($sortBy === 'date') {
            $sortBy = 'created_at';
        } elseif ($sortBy === 'price') {
            $sortBy = 'total_price_after_discount';
        }
        
        $query->orderBy($sortBy, $sortDirection);
        
        // Pagination
        $perPage = $request->per_page ?? 15;
        $orders = $query->with(['driver:id,name,phone,photo', 'service:id,name'])->paginate($perPage);
        
        // Transform data to include status text and other helper methods
        $orders->getCollection()->transform(function ($order) {
            $order->status_text = $order->getStatusText();
            $order->payment_method_text = $order->getPaymentMethodText();
            $order->payment_status_text = $order->getPaymentStatusText();
            $order->distance = $order->getDistance();
            return $order;
        });
        
        $responseData = [
            'orders' => $orders,
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total()
            ]
        ];
        
        return $this->success_response('Orders retrieved successfully', $responseData);
    }
    
    /**
     * Get active orders (pending, accepted, in progress)
     *
     * @return \Illuminate\Http\Response
     */
    public function activeOrders()
    {
        $user = Auth::user();
        
        $orders = Order::where('user_id', $user->id)
            ->whereIn('status', [1, 2, 3, 4]) // Pending or in progress orders
            ->with(['driver:id,name,phone,photo,fcm_token', 'service:id,name'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Transform data to include status text and other helper methods
        $orders->transform(function ($order) {
            $order->status_text = $order->getStatusText();
            $order->payment_method_text = $order->getPaymentMethodText();
            $order->distance = $order->getDistance();
            return $order;
        });
        
        return $this->success_response('Active orders retrieved successfully', $orders);
    }
    
    /**
     * Get completed orders history
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function completedOrders(Request $request)
    {
        $user = Auth::user();
        
        $validator = Validator::make($request->all(), [
            'per_page' => 'sometimes|integer|min:5|max:100',
            'from_date' => 'sometimes|date_format:Y-m-d',
            'to_date' => 'sometimes|date_format:Y-m-d|after_or_equal:from_date'
        ]);
        
        if ($validator->fails()) {
            return $this->error_response('Validation error', $validator->errors());
        }
        
        $query = Order::where('user_id', $user->id)
            ->where('status', 5); // Only completed orders
        
        // Filter by date range if provided
        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        
        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }
        
        // Pagination
        $perPage = $request->per_page ?? 15;
        $orders = $query->with(['driver:id,name,phone,photo', 'service:id,name'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
        
        // Transform data to include status text and other helper methods
        $orders->getCollection()->transform(function ($order) {
            $order->status_text = $order->getStatusText();
            $order->payment_method_text = $order->getPaymentMethodText();
            $order->payment_status_text = $order->getPaymentStatusText();
            $order->distance = $order->getDistance();
            return $order;
        });
        
        $responseData = [
            'orders' => $orders,
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total()
            ]
        ];
        
        return $this->success_response('Completed orders retrieved successfully', $responseData);
    }
    
    /**
     * Get cancelled orders
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function cancelledOrders(Request $request)
    {
        $user = Auth::user();
        
        $validator = Validator::make($request->all(), [
            'per_page' => 'sometimes|integer|min:5|max:100',
            'cancelled_by' => 'sometimes|in:user,driver,all'
        ]);
        
        if ($validator->fails()) {
            return $this->error_response('Validation error', $validator->errors());
        }
        
        $query = Order::where('user_id', $user->id);
        
        // Filter by who cancelled
        $cancelledBy = $request->cancelled_by ?? 'all';
        if ($cancelledBy === 'user') {
            $query->where('status', 6); // Cancelled by user
        } elseif ($cancelledBy === 'driver') {
            $query->where('status', 7); // Cancelled by driver
        } else {
            $query->whereIn('status', [6, 7]); // All cancelled orders
        }
        
        // Pagination
        $perPage = $request->per_page ?? 15;
        $orders = $query->with(['driver:id,name,phone,photo', 'service:id,name'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
        
        // Transform data to include status text and other helper methods
        $orders->getCollection()->transform(function ($order) {
            $order->status_text = $order->getStatusText();
            $order->payment_method_text = $order->getPaymentMethodText();
            $order->distance = $order->getDistance();
            return $order;
        });
        
        $responseData = [
            'orders' => $orders,
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total()
            ]
        ];
        
        return $this->success_response('Cancelled orders retrieved successfully', $responseData);
    }
    
    /**
     * Store a newly created order
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        $validator = Validator::make($request->all(), [
            'service_id' => 'required|exists:services,id',
            'pick_name' => 'required|string|max:255',
            'pick_lat' => 'required|numeric',
            'pick_lng' => 'required|numeric',
            'drop_name' => 'required|string|max:255',
            'drop_lat' => 'required|numeric',
            'drop_lng' => 'required|numeric',
            'payment_method' => 'required|in:1,2,3'
        ]);
        
        if ($validator->fails()) {
            return $this->error_response('Validation error', $validator->errors());
        }
        
        // Get service details
        $service = Service::find($request->service_id);
        if (!$service) {
            return $this->error_response('Service not found', null);
        }
        
        $price = $request->total_price_before_discount;
        
        // Apply discount if user has one (this is a placeholder - implement your discount logic)
        $discount = 0;
        $userHasDiscount = false; // Implement your discount checking logic
        
        if ($userHasDiscount) {
            // Apply discount logic
            $discount = 0; // Replace with your discount calculation
        }
        
        $priceAfterDiscount = $price - $discount;
        
        // Calculate commission and driver's net price
        $commissionRate = 0.15; // 15% - adjust based on your business model
        $commissionAmount = $priceAfterDiscount * $commissionRate;
        $driverNetPrice = $priceAfterDiscount - $commissionAmount;
        
        // Check payment method and verify wallet balance if needed
        if ($request->payment_method == 3) { // Wallet payment
            if ($user->balance < $priceAfterDiscount) {
                return $this->error_response('Insufficient wallet balance', [
                    'available_balance' => $user->balance,
                    'required_amount' => $priceAfterDiscount
                ]);
            }
        }
        
        // Create order
        $order = new Order();
        $order->user_id = $user->id;
        $order->service_id = $request->service_id;
        $order->pick_name = $request->pick_name;
        $order->pick_lat = $request->pick_lat;
        $order->pick_lng = $request->pick_lng;
        $order->drop_name = $request->drop_name;
        $order->drop_lat = $request->drop_lat;
        $order->drop_lng = $request->drop_lng;
        $order->total_price_before_discount = $price;
        $order->discount_value = $discount;
        $order->total_price_after_discount = $priceAfterDiscount;
        $order->net_price_for_driver = $driverNetPrice;
        $order->commision_of_admin = $commissionAmount;
        $order->status = 1; // Pending
        $order->payment_method = $request->payment_method;
        $order->status_payment = ($request->payment_method == 3) ? 2 : 1; // If wallet payment, mark as paid
        $order->save();
        
        // If payment method is wallet, deduct amount from user's wallet
        if ($request->payment_method == 3) {
            $user->balance -= $priceAfterDiscount;
            $user->save();
            
            // Create wallet transaction record (if you have a wallet_transactions table)
            // This is a placeholder - implement based on your wallet_transactions table structure
            /* 
            WalletTransaction::create([
                'user_id' => $user->id,
                'amount' => $priceAfterDiscount,
                'type_of_transaction' => 2, // Deduction
                'note' => 'Payment for order #' . $order->id
            ]);
            */
        }
        
        // Add helper attributes
        $order->status_text = $order->getStatusText();
        $order->payment_method_text = $order->getPaymentMethodText();
        $order->payment_status_text = $order->getPaymentStatusText();
        $order->distance = $order->getDistance();
        
        // TODO: Notify nearby drivers about new order
        // Implement your driver notification logic here
        
        return $this->success_response('Order created successfully', $order);
    }
    
    /**
     * Display the specified order
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = Auth::user();
        
        $order = Order::where('id', $id)
            ->where('user_id', $user->id)
            ->with([
                'driver:id,name,phone,photo,fcm_token',
                'service:id,name,description,photo'
            ])
            ->first();
        
        if (!$order) {
            return $this->error_response('Order not found', null);
        }
        
        // Add helper attributes
        $order->status_text = $order->getStatusText();
        $order->payment_method_text = $order->getPaymentMethodText();
        $order->payment_status_text = $order->getPaymentStatusText();
        $order->distance = $order->getDistance();
        $order->discount_percentage = $order->getDiscountPercentage();
        
        return $this->success_response('Order details retrieved successfully', $order);
    }
    
    /**
     * Cancel an order by the user
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function cancelOrder(Request $request, $id)
    {
        $user = Auth::user();
        
        $order = Order::where('id', $id)
            ->where('user_id', $user->id)
            ->first();
        
        if (!$order) {
            return $this->error_response('Order not found', null);
        }
        
        // Check if order can be cancelled
        if (!in_array($order->status, [1, 2])) {
            return $this->error_response('Order cannot be cancelled at this stage', null);
        }
        
        $validator = Validator::make($request->all(), [
            'reason_for_cancel' => 'required|string|max:255'
        ]);
        
        if ($validator->fails()) {
            return $this->error_response('Validation error', $validator->errors());
        }
        
        // Process cancellation
        $order->status = 6; // User cancelled order
        $order->reason_for_cancel = $request->reason_for_cancel;
        $order->save();
        
        // If payment was made through wallet, refund the amount
        if ($order->payment_method == 3 && $order->status_payment == 2) {
            $user->balance += $order->total_price_after_discount;
            $user->save();
            
            // Create wallet transaction record for refund
            // This is a placeholder - implement based on your wallet_transactions table structure
            /* 
            WalletTransaction::create([
                'user_id' => $user->id,
                'amount' => $order->total_price_after_discount,
                'type_of_transaction' => 1, // Addition
                'note' => 'Refund for cancelled order #' . $order->id
            ]);
            */
        }
        
        // Notify driver if already assigned
        if ($order->driver_id) {
            // Implement notification logic to driver about cancellation
            // You can use FCM or other push notification service
        }
        
        $responseData = [
            'order_id' => $order->id,
            'status' => $order->status,
            'status_text' => $order->getStatusText(),
            'cancellation_reason' => $order->reason_for_cancel
        ];
        
        return $this->success_response('Order cancelled successfully', $responseData);
    }
}