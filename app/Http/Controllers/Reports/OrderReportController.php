<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderReportController extends Controller
{
    public function index()
    {
        // Get delivery options for filter dropdown with place names
        $deliveries = DB::table('deliveries')
            ->select('id', 'place')
            ->orderBy('place')
            ->get();

        return view('reports.ordersReport', compact('deliveries'));
    }

    public function generate(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'order_status' => 'nullable|array',
            'order_status.*' => 'integer|in:1,2,3,4,5,6',
            'payment_status' => 'nullable|integer|in:1,2',
            'payment_type' => 'nullable|string',
            'user_id' => 'nullable|exists:users,id',
            'delivery_id' => 'nullable|integer', // Add delivery_id validation
            'report_type' => 'required|in:summary,detailed'
        ]);

        $startDate = Carbon::parse($request->start_date)->startOfDay();
        $endDate = Carbon::parse($request->end_date)->endOfDay();

        // Base query for orders
        $ordersQuery = DB::table('orders')
            ->leftJoin('users', 'orders.user_id', '=', 'users.id')
            ->leftJoin('user_addresses', 'orders.address_id', '=', 'user_addresses.id')
            ->whereBetween('orders.date', [$startDate, $endDate]);

        // Apply filters
        if ($request->filled('order_status')) {
            $ordersQuery->whereIn('orders.order_status', $request->order_status);
        }

        if ($request->filled('payment_status')) {
            $ordersQuery->where('orders.payment_status', $request->payment_status);
        }

        if ($request->filled('payment_type')) {
            $ordersQuery->where('orders.payment_type', $request->payment_type);
        }

        if ($request->filled('user_id')) {
            $ordersQuery->where('orders.user_id', $request->user_id);
        }

        // Add delivery filter
        if ($request->filled('delivery_id')) {
            $ordersQuery->where('user_addresses.delivery_id', $request->delivery_id);
        }

        // Generate summary statistics
        $summary = $this->generateSummary($ordersQuery, $startDate, $endDate);

        if ($request->report_type === 'summary') {
            $orders = collect();
            $dailyStats = $this->getDailyStats($startDate, $endDate, $request);
            $topProducts = $this->getTopProducts($startDate, $endDate, $request);
        } else {
            // Get detailed orders
            $orders = $ordersQuery
                ->select(
                    'orders.*',
                    'users.name as customer_name',
                    'users.email as customer_email',
                    'users.phone as customer_phone',
                    'user_addresses.address_line_1',
                    'user_addresses.city',
                    'user_addresses.state',
                    'user_addresses.delivery_id',
                    'deliveries.place as delivery_place' // Add delivery place to select
                )
                ->leftJoin('deliveries', 'user_addresses.delivery_id', '=', 'deliveries.id') // Join with deliveries table
                ->orderBy('orders.date', 'desc')
                ->paginate(50)
                ->withQueryString();

            $dailyStats = collect();
            $topProducts = collect();
        }

        // Get users for filter dropdown
        $users = DB::table('users')
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->get();

        // Get payment types
        $paymentTypes = DB::table('orders')
            ->select('payment_type')
            ->distinct()
            ->whereNotNull('payment_type')
            ->pluck('payment_type');

        // Get deliveries for filter dropdown with place names
        $deliveries = DB::table('deliveries')
            ->select('id', 'place')
            ->orderBy('place')
            ->get();

        return view('reports.ordersReport', compact(
            'orders',
            'summary',
            'dailyStats',
            'topProducts',
            'users',
            'paymentTypes',
            'deliveries', // Add deliveries to compact
            'startDate',
            'endDate'
        ));
    }

    private function generateSummary($query, $startDate, $endDate)
    {
        $baseQuery = clone $query;
        
        return [
            'total_orders' => $baseQuery->count(),
            'total_revenue' => $baseQuery->sum('total_prices'),
            'total_taxes' => $baseQuery->sum('total_taxes'),
            'total_delivery_fees' => $baseQuery->sum('delivery_fee'),
            'total_discounts' => $baseQuery->sum('total_discounts'),
            'total_coupon_discounts' => $baseQuery->sum('coupon_discount'),
            'pending_orders' => (clone $baseQuery)->where('order_status', 1)->count(),
            'accepted_orders' => (clone $baseQuery)->where('order_status', 2)->count(),
            'on_the_way_orders' => (clone $baseQuery)->where('order_status', 3)->count(),
            'delivered_orders' => (clone $baseQuery)->where('order_status', 4)->count(),
            'canceled_orders' => (clone $baseQuery)->where('order_status', 5)->count(),
            'refund_orders' => (clone $baseQuery)->where('order_status', 6)->count(),
            'paid_orders' => (clone $baseQuery)->where('payment_status', 1)->count(),
            'unpaid_orders' => (clone $baseQuery)->where('payment_status', 2)->count(),
            'average_order_value' => $baseQuery->avg('total_prices'),
            'date_range' => $startDate->format('Y-m-d') . ' to ' . $endDate->format('Y-m-d')
        ];
    }

    private function getDailyStats($startDate, $endDate, $request)
    {
        $query = DB::table('orders')
            ->leftJoin('user_addresses', 'orders.address_id', '=', 'user_addresses.id') // Add join for delivery filter
            ->whereBetween('orders.date', [$startDate, $endDate]);

        // Apply same filters as main query
        if ($request->filled('order_status')) {
            $query->whereIn('orders.order_status', $request->order_status);
        }
        if ($request->filled('payment_status')) {
            $query->where('orders.payment_status', $request->payment_status);
        }
        if ($request->filled('payment_type')) {
            $query->where('orders.payment_type', $request->payment_type);
        }
        if ($request->filled('user_id')) {
            $query->where('orders.user_id', $request->user_id);
        }
        // Add delivery filter to daily stats
        if ($request->filled('delivery_id')) {
            $query->where('user_addresses.delivery_id', $request->delivery_id);
        }

        return $query
            ->select(
                DB::raw('DATE(orders.date) as order_date'),
                DB::raw('COUNT(*) as orders_count'),
                DB::raw('SUM(orders.total_prices) as daily_revenue'),
                DB::raw('AVG(orders.total_prices) as avg_order_value')
            )
            ->groupBy(DB::raw('DATE(orders.date)'))
            ->orderBy('order_date')
            ->get();
    }

    private function getTopProducts($startDate, $endDate, $request)
    {
        $query = DB::table('order_products')
            ->join('orders', 'order_products.order_id', '=', 'orders.id')
            ->join('products', 'order_products.product_id', '=', 'products.id')
            ->leftJoin('user_addresses', 'orders.address_id', '=', 'user_addresses.id') // Add join for delivery filter
            ->whereBetween('orders.date', [$startDate, $endDate]);

        // Apply same filters as main query
        if ($request->filled('order_status')) {
            $query->whereIn('orders.order_status', $request->order_status);
        }
        if ($request->filled('payment_status')) {
            $query->where('orders.payment_status', $request->payment_status);
        }
        if ($request->filled('payment_type')) {
            $query->where('orders.payment_type', $request->payment_type);
        }
        if ($request->filled('user_id')) {
            $query->where('orders.user_id', $request->user_id);
        }
        // Add delivery filter to top products
        if ($request->filled('delivery_id')) {
            $query->where('user_addresses.delivery_id', $request->delivery_id);
        }

        return $query
            ->select(
                'products.name_en',
                'products.name_ar',
                DB::raw('SUM(order_products.quantity) as total_quantity'),
                DB::raw('SUM(order_products.total_price_after_tax) as total_revenue'),
                DB::raw('COUNT(DISTINCT order_products.order_id) as orders_count')
            )
            ->groupBy('products.id', 'products.name_en', 'products.name_ar')
            ->orderBy('total_quantity', 'desc')
            ->limit(10)
            ->get();
    }

    public function export(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $startDate = Carbon::parse($request->start_date)->startOfDay();
        $endDate = Carbon::parse($request->end_date)->endOfDay();

        $orders = DB::table('orders')
            ->leftJoin('users', 'orders.user_id', '=', 'users.id')
            ->leftJoin('user_addresses', 'orders.address_id', '=', 'user_addresses.id') // Add join for export
            ->leftJoin('deliveries', 'user_addresses.delivery_id', '=', 'deliveries.id') // Join with deliveries table
            ->whereBetween('orders.date', [$startDate, $endDate]);

        // Apply delivery filter to export if present
        if ($request->filled('delivery_id')) {
            $orders->where('user_addresses.delivery_id', $request->delivery_id);
        }

        $orders = $orders->select(
                'orders.number',
                'orders.date',
                'orders.order_status',
                'orders.payment_status',
                'orders.payment_type',
                'orders.total_prices',
                'orders.total_taxes',
                'orders.delivery_fee',
                'orders.total_discounts',
                'users.name as customer_name',
                'users.email as customer_email',
                'user_addresses.delivery_id',
                'deliveries.place as delivery_place' // Add delivery place to export
            )
            ->orderBy('orders.date', 'desc')
            ->get();

        $filename = "orders_report_" . $startDate->format('Y-m-d') . "_to_" . $endDate->format('Y-m-d') . ".csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($orders) {
            $file = fopen('php://output', 'w');
            
            // CSV Headers
            fputcsv($file, [
                'Order Number',
                'Date',
                'Status',
                'Payment Status',
                'Payment Type',
                'Total Price',
                'Taxes',
                'Delivery Fee',
                'Discounts',
                'Customer Name',
                'Customer Email',
                'Delivery ID',
                'Delivery Place' // Add delivery place to CSV headers
            ]);

            // CSV Data
            foreach ($orders as $order) {
                fputcsv($file, [
                    $order->number,
                    $order->date,
                    $this->getOrderStatusText($order->order_status),
                    $order->payment_status == 1 ? 'Paid' : 'Unpaid',
                    $order->payment_type,
                    $order->total_prices,
                    $order->total_taxes,
                    $order->delivery_fee,
                    $order->total_discounts,
                    $order->customer_name,
                    $order->customer_email,
                    $order->delivery_id,
                    $order->delivery_place // Add delivery place to CSV data
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function getOrderStatusText($status)
    {
        $statuses = [
            1 => 'Pending',
            2 => 'Accepted',
            3 => 'On The Way',
            4 => 'Delivered',
            5 => 'Canceled',
            6 => 'Refund'
        ];

        return $statuses[$status] ?? 'Unknown';
    }
}