<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\Appointment;
use App\Models\User;
use App\Models\Driver;
use App\Models\ProviderType;
use App\Models\Service;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AppointmentController extends Controller
{
     public function index(Request $request)
    {
        try {
            $query = Appointment::with([
                'user:id,name,phone,email',
                'address',
                'providerType:id,name,description,price_per_hour,is_vip',
                'providerType.provider:id,name_of_manager,phone',
                'providerType.type:id,name'
            ])->orderBy('created_at', 'desc');

            // Filter by appointment status
            if ($request->filled('appointment_status')) {
                $query->where('appointment_status', $request->appointment_status);
            }

            // Filter by payment status
            if ($request->filled('payment_status')) {
                $query->where('payment_status', $request->payment_status);
            }

            // Filter by payment type
            if ($request->filled('payment_type')) {
                $query->where('payment_type', $request->payment_type);
            }

            // Filter by provider type
            if ($request->filled('provider_type_id')) {
                $query->where('provider_type_id', $request->provider_type_id);
            }

            // Filter by VIP status
            if ($request->filled('is_vip')) {
                $query->whereHas('providerType', function($q) use ($request) {
                    $q->where('is_vip', $request->is_vip);
                });
            }

            // Filter by date range
            if ($request->filled('from_date')) {
                $query->whereDate('date', '>=', $request->from_date);
            }

            if ($request->filled('to_date')) {
                $query->whereDate('date', '<=', $request->to_date);
            }

            // Search by appointment number or user name
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('number', 'like', "%{$search}%")
                      ->orWhereHas('user', function($userQuery) use ($search) {
                          $userQuery->where('name', 'like', "%{$search}%")
                                   ->orWhere('phone', 'like', "%{$search}%");
                      })
                      ->orWhereHas('providerType', function($providerQuery) use ($search) {
                          $providerQuery->where('name', 'like', "%{$search}%");
                      });
                });
            }

            $appointments = $query->paginate(15);

            // Add status labels
            $appointments->getCollection()->transform(function ($appointment) {
                $appointment->appointment_status_label = $this->getAppointmentStatusLabel($appointment->appointment_status);
                $appointment->payment_status_label = $this->getPaymentStatusLabel($appointment->payment_status);
                $appointment->is_vip_label = $appointment->providerType->is_vip == 1 ? 'VIP' : 'Regular';
                return $appointment;
            });

            // Get statistics and provider types for filters
            $statistics = $this->getAppointmentStatistics();
            $providerTypes = ProviderType::where('activate', 1)->get(['id', 'name']);

            return view('admin.appointments.index', compact('appointments', 'statistics', 'providerTypes'));

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to load appointments: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified appointment
     */
    public function show($id)
    {
        try {
            $appointment = Appointment::with([
                'user:id,name,phone,email,country_code',
                'address',
                'providerType:id,name,description,address,lat,lng,price_per_hour,status,is_vip',
                'providerType.provider:id,name_of_manager,phone,email',
                'providerType.type:id,name,description'
            ])->findOrFail($id);

            $appointment->appointment_status_label = $this->getAppointmentStatusLabel($appointment->appointment_status);
            $appointment->payment_status_label = $this->getPaymentStatusLabel($appointment->payment_status);
            $appointment->is_vip_label = $appointment->providerType->is_vip == 1 ? 'VIP' : 'Regular';

            return view('admin.appointments.show', compact('appointment'));

        } catch (\Exception $e) {
            return back()->with('error', 'Appointment not found: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified appointment
     */
    public function edit($id)
    {
        try {
            $appointment = Appointment::with([
                'user:id,name,phone,email',
                'address',
                'providerType'
            ])->findOrFail($id);

            $users = User::where('activate', 1)->get(['id', 'name', 'phone', 'email']);
            $providerTypes = ProviderType::where('activate', 1)->where('status', 1)->get(['id', 'name', 'price_per_hour']);
            
            return view('admin.appointments.edit', compact('appointment', 'users', 'providerTypes'));

        } catch (\Exception $e) {
            return back()->with('error', 'Appointment not found: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified appointment
     */
    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'appointment_status' => 'required|integer|in:1,2,3,4,5',
                'payment_status' => 'required|integer|in:1,2',
                'date' => 'required|date',
                'note' => 'nullable|string|max:500'
            ]);

            $appointment = Appointment::findOrFail($id);
            $oldStatus = $appointment->appointment_status;
            $oldPaymentStatus = $appointment->payment_status;

            DB::beginTransaction();

            try {
                // Update appointment
                $appointment->update([
                    'appointment_status' => $request->appointment_status,
                    'payment_status' => $request->payment_status,
                    'date' => $request->date,
                    'note' => $request->note
                ]);

                // Handle payment status change
                if ($oldPaymentStatus != $request->payment_status) {
                    $this->handlePaymentStatusChange($appointment, $request->payment_status);
                }

                // Handle cancellation refund
                if ($request->appointment_status == 5 && $oldStatus != 5 && $appointment->payment_status == 1) {
                    $this->processCancellationRefund($appointment);
                }

                DB::commit();

                return redirect()->route('admin.appointments.show', $appointment->id)
                    ->with('success', 'Appointment updated successfully');

            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update appointment: ' . $e->getMessage());
        }
    }

    /**
     * Get appointment statistics
     */
    private function getAppointmentStatistics()
    {
        return [
            'total_appointments' => Appointment::count(),
            'pending_appointments' => Appointment::where('appointment_status', 1)->count(),
            'completed_appointments' => Appointment::where('appointment_status', 4)->count(),
            'canceled_appointments' => Appointment::where('appointment_status', 5)->count(),
            'total_revenue' => Appointment::where('appointment_status', 4)->sum('total_prices'),
            'unpaid_appointments' => Appointment::where('payment_status', 2)->count(),
            'vip_appointments' => Appointment::whereHas('providerType', function($q) {
                $q->where('is_vip', 1);
            })->count(),
            'today_appointments' => Appointment::whereDate('date', today())->count(),
            'this_month_appointments' => Appointment::whereMonth('created_at', now()->month)
                                                  ->whereYear('created_at', now()->year)
                                                  ->count()
        ];
    }

    /**
     * Handle payment status change
     */
    private function handlePaymentStatusChange($appointment, $newPaymentStatus)
    {
        if ($newPaymentStatus == 1 && $appointment->payment_type == 'wallet') {
            // Deduct from user wallet if payment is marked as paid
            $user = $appointment->user;
            if ($user->balance >= $appointment->total_prices) {
                $user->decrement('balance', $appointment->total_prices);
                
                // Create wallet transaction
                WalletTransaction::create([
                    'user_id' => $user->id,
                    'amount' => $appointment->total_prices,
                    'type_of_transaction' => 2, // withdrawal
                    'note' => "Payment for appointment #{$appointment->number}"
                ]);
            }
        }
    }

    /**
     * Process cancellation refund
     */
    private function processCancellationRefund($appointment)
    {
        $user = $appointment->user;
        
        // Add refund to user wallet
        $user->increment('balance', $appointment->total_prices);
        
        // Create wallet transaction for refund
        WalletTransaction::create([
            'user_id' => $user->id,
            'amount' => $appointment->total_prices,
            'type_of_transaction' => 1, // add
            'note' => "Refund for canceled appointment #{$appointment->number}"
        ]);
    }

    /**
     * Get appointment status label
     */
    private function getAppointmentStatusLabel($status)
    {
        $labels = [
            1 => 'Pending',
            2 => 'Accepted',
            3 => 'On The Way', 
            4 => 'Delivered',
            5 => 'Canceled'
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