<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Teacher;
use App\Models\Clas; // Replace with your actual class model name
use App\Models\Driver;
use App\Models\Order;
use App\Models\Product;
use App\Models\Provider;
use App\Models\ProviderType;
use App\Models\Setting;
use App\Models\VoucherProduct;
use Carbon\Carbon;

class DashboardController extends Controller
{
     public function index()
    {
        // Count all entities
        $usersCount = User::count();

        $providersCount = ProviderType::whereHas('type', function($query) {
            $query->where('booking_type', 'hourly');
        })->count();
        
        $salonsCount = ProviderType::whereHas('type', function($query) {
            $query->where('booking_type', 'service');
        })->count();
        
        // Orders statistics
        $totalOrders = Order::count();
        $activeOrders = Order::whereIn('order_status', [1, 2, 3])->count(); // Pending, Accepted, OnTheWay
        $completedOrders = Order::where('order_status', 4)->count(); // Delivered
        $canceledOrders = Order::where('order_status', 5)->count(); // Canceled
        
        // Appointments statistics
        $totalAppointments = Appointment::count();
        $activeAppointments = Appointment::whereIn('appointment_status', [1, 2, 3, 6, 7])->count(); // Active statuses
        $completedAppointments = Appointment::where('appointment_status', 4)->count(); // Delivered
        $canceledAppointments = Appointment::where('appointment_status', 5)->count(); // Canceled
        
        // Total sales (from both orders and appointments)
        $totalSalesFromOrders = Order::where('order_status', 4)->sum('total_prices'); // Only completed orders
        $totalSalesFromAppointments = Appointment::where('appointment_status', 4)->sum('total_prices'); // Only completed appointments
        $totalSales = $totalSalesFromOrders + $totalSalesFromAppointments;
        
        // Late/delayed orders and appointments (assuming orders/appointments older than 24 hours and still pending)
        $lateOrders = Order::where('order_status', 1)
            ->where('created_at', '<', Carbon::now()->subHours(24))
            ->count();
            
        $lateAppointments = Appointment::where('appointment_status', 1)
            ->where('created_at', '<', Carbon::now()->subHours(24))
            ->count();
            
        $totalLateRequests = $lateOrders + $lateAppointments;
        
        $lowStockProducts = VoucherProduct::getLowStockProducts();
        $lowStockCount = VoucherProduct::getLowStockCount();
        $minimumQuantityThreshold = Setting::getValue('minimum_to_notify_me_for_quantity_products', 2);
        
        return view('admin.dashboard', compact(
            'lowStockProducts',
            'lowStockCount',
            'minimumQuantityThreshold',
            'usersCount',
            'providersCount', 
            'salonsCount',
            'totalOrders',
            'totalAppointments',
            'activeOrders',
            'activeAppointments',
            'completedOrders',
            'completedAppointments',
            'canceledOrders',
            'canceledAppointments',
            'totalSales',
            'totalLateRequests'
        ));
    }

}
