<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\Appointment;
use App\Models\User;
use App\Models\Setting;
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
                'providerType',
                'providerType.provider',
                'providerType.type',
                'appointmentServices.service'
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

            // Filter by booking type (hourly or service)
            if ($request->filled('booking_type')) {
                $query->whereHas('providerType.type', function($q) use ($request) {
                    $q->where('booking_type', $request->booking_type);
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
                      })
                      ->orWhereHas('appointmentServices.service', function($serviceQuery) use ($search) {
                          $serviceQuery->where('name_en', 'like', "%{$search}%")
                                      ->orWhere('name_ar', 'like', "%{$search}%");
                      });
                });
            }

            $appointments = $query->paginate(15);

            // Add status labels and booking type info
            $appointments->getCollection()->transform(function ($appointment) {
                $appointment->appointment_status_label = $this->getAppointmentStatusLabel($appointment->appointment_status);
                $appointment->payment_status_label = $this->getPaymentStatusLabel($appointment->payment_status);
                $appointment->is_vip_label = $appointment->providerType->is_vip == 1 ? 'VIP' : 'Regular';
                $appointment->booking_type = $appointment->providerType->type->booking_type ?? 'hourly';
                $appointment->total_customers = $this->getTotalCustomers($appointment);
                $appointment->services_summary = $this->getServicesSummary($appointment);
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
                'providerType',
                'providerType.provider',
                'providerType.type',
                'appointmentServices.service'
            ])->findOrFail($id);

            $appointment->appointment_status_label = $this->getAppointmentStatusLabel($appointment->appointment_status);
            $appointment->payment_status_label = $this->getPaymentStatusLabel($appointment->payment_status);
            $appointment->is_vip_label = $appointment->providerType->is_vip == 1 ? 'VIP' : 'Regular';
            $appointment->booking_type = $appointment->providerType->type->booking_type ?? 'hourly';
            $appointment->total_customers = $this->getTotalCustomers($appointment);
            $appointment->commission_details = $this->getCommissionDetails($appointment);
            $appointment->services_summary = $this->getServicesSummary($appointment);

            return view('admin.appointments.show', compact('appointment'));

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

            $appointment = Appointment::with(['user', 'providerType.provider'])->findOrFail($id);
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

                // Handle payment status change to paid
                if ($oldPaymentStatus == 2 && $request->payment_status == 1) {
                    $this->processPayment($appointment);
                }

                // Handle completion (status = 4) - transfer pending amounts
                if ($request->appointment_status == 4 && $oldStatus != 4) {
                    $this->processCompletionPayments($appointment);
                }

                // Handle cancellation refund
                if ($request->appointment_status == 5 && $oldStatus != 5) {
                    $this->processCancellation($appointment);
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
     * Process payment based on payment type
     */
    private function processPayment($appointment)
    {
        $commission = $this->getAdminCommission();
        $commissionAmount = ($appointment->total_prices * $commission) / 100;
        $providerAmount = $appointment->total_prices - $commissionAmount;
        $provider = $appointment->providerType->provider;

        switch ($appointment->payment_type) {
            case 'cash':
                $this->processCashPayment($appointment, $provider, $commissionAmount);
                break;
                
            case 'visa':
                $this->processVisaPayment($appointment, $provider, $commissionAmount, $providerAmount);
                break;
                
            case 'wallet':
                $this->processWalletPayment($appointment, $provider, $commissionAmount, $providerAmount);
                break;
        }
    }

    /**
     * Process cash payment
     */
    private function processCashPayment($appointment, $provider, $commissionAmount)
    {
        // Deduct commission from provider wallet
        $provider->decrement('balance', $commissionAmount);
        
        // Create provider transaction (withdrawal)
        WalletTransaction::create([
            'provider_id' => $provider->id,
            'amount' => $commissionAmount,
            'type_of_transaction' => 2, // withdrawal
            'note' => "Commission deduction for cash appointment #{$appointment->number}"
        ]);

        // Add commission to admin wallet (pending)
        WalletTransaction::create([
            'admin_id' => 1, // Assuming admin ID is 1, adjust as needed
            'amount' => $commissionAmount,
            'type_of_transaction' => 1, // add
            'note' => "Commission from cash appointment #{$appointment->number} (Pending)"
        ]);
    }

    /**
     * Process visa payment
     */
    private function processVisaPayment($appointment, $provider, $commissionAmount, $providerAmount)
    {
        // Add provider amount to provider wallet (pending)
        $provider->increment('balance', $providerAmount);
        
        WalletTransaction::create([
            'provider_id' => $provider->id,
            'amount' => $providerAmount,
            'type_of_transaction' => 1, // add
            'note' => "Payment from visa appointment #{$appointment->number} (Pending)"
        ]);

        // Add commission to admin wallet (pending)
        WalletTransaction::create([
            'admin_id' => 1, // Assuming admin ID is 1
            'amount' => $commissionAmount,
            'type_of_transaction' => 1, // add
            'note' => "Commission from visa appointment #{$appointment->number} (Pending)"
        ]);
    }

    /**
     * Process wallet payment
     */
    private function processWalletPayment($appointment, $provider, $commissionAmount, $providerAmount)
    {
        $user = $appointment->user;
        
        // Deduct total from user wallet
        $user->decrement('balance', $appointment->total_prices);
        
        WalletTransaction::create([
            'user_id' => $user->id,
            'amount' => $appointment->total_prices,
            'type_of_transaction' => 2, // withdrawal
            'note' => "Payment for appointment #{$appointment->number}"
        ]);

        // Add provider amount to provider wallet
        $provider->increment('balance', $providerAmount);
        
        WalletTransaction::create([
            'provider_id' => $provider->id,
            'amount' => $providerAmount,
            'type_of_transaction' => 1, // add
            'note' => "Payment from wallet appointment #{$appointment->number}"
        ]);

        // Add commission to admin wallet
        WalletTransaction::create([
            'admin_id' => 1, // Assuming admin ID is 1
            'amount' => $commissionAmount,
            'type_of_transaction' => 1, // add
            'note' => "Commission from wallet appointment #{$appointment->number}"
        ]);
    }

    /**
     * Process completion payments (transfer pending amounts)
     */
    private function processCompletionPayments($appointment)
    {
        // Mark pending transactions as completed (you might want to add a status column to wallet_transactions)
        // For now, we'll just add a completion note
        
        WalletTransaction::create([
            'admin_id' => 1,
            'amount' => 0, // Just a note, no amount change
            'type_of_transaction' => 1,
            'note' => "Appointment #{$appointment->number} completed - pending amounts transferred"
        ]);
    }

    /**
     * Process cancellation
     */
    private function processCancellation($appointment)
    {
        if ($appointment->payment_status == 1) { // Only if already paid
            $user = $appointment->user;
            
            // Refund to user wallet
            $user->increment('balance', $appointment->total_prices);
            
            WalletTransaction::create([
                'user_id' => $user->id,
                'amount' => $appointment->total_prices,
                'type_of_transaction' => 1, // add
                'note' => "Refund for canceled appointment #{$appointment->number}"
            ]);

            // Reverse commission transactions if needed
            $this->reverseCommissionTransactions($appointment);
        }
    }

    /**
     * Reverse commission transactions for cancellations
     */
    private function reverseCommissionTransactions($appointment)
    {
        $commission = $this->getAdminCommission();
        $commissionAmount = ($appointment->total_prices * $commission) / 100;
        $provider = $appointment->providerType->provider;

        switch ($appointment->payment_type) {
            case 'cash':
                // Add commission back to provider wallet
                $provider->increment('balance', $commissionAmount);
                WalletTransaction::create([
                    'provider_id' => $provider->id,
                    'amount' => $commissionAmount,
                    'type_of_transaction' => 1, // add
                    'note' => "Commission refund for canceled appointment #{$appointment->number}"
                ]);
                break;
                
            case 'visa':
                $providerAmount = $appointment->total_prices - $commissionAmount;
                // Deduct provider amount from provider wallet
                $provider->decrement('balance', $providerAmount);
                WalletTransaction::create([
                    'provider_id' => $provider->id,
                    'amount' => $providerAmount,
                    'type_of_transaction' => 2, // withdrawal
                    'note' => "Payment reversal for canceled appointment #{$appointment->number}"
                ]);
                break;
                
            case 'wallet':
                // Already handled in main refund
                break;
        }
    }

    /**
     * Get total customers for an appointment
     */
    private function getTotalCustomers($appointment)
    {
        if (isset($appointment->providerType->type->booking_type) && 
            $appointment->providerType->type->booking_type == 'service') {
            return $appointment->appointmentServices->sum('customer_count');
        }
        return 1; // Default for hourly appointments
    }

    /**
     * Get services summary for service-based appointments
     */
    private function getServicesSummary($appointment)
    {
        if (isset($appointment->providerType->type->booking_type) && 
            $appointment->providerType->type->booking_type == 'service') {
            
            $services = $appointment->appointmentServices->map(function($appointmentService) {
                return [
                    'name' => app()->getLocale() == 'ar' ? 
                        $appointmentService->service->name_ar : 
                        $appointmentService->service->name_en,
                    'customer_count' => $appointmentService->customer_count,
                    'service_price' => $appointmentService->service_price,
                    'total_price' => $appointmentService->total_price
                ];
            });

            return [
                'services' => $services,
                'total_services' => $services->count(),
                'total_customers' => $services->sum('customer_count'),
                'services_total' => $services->sum('total_price')
            ];
        }
        
        return [
            'services' => collect(),
            'total_services' => 0,
            'total_customers' => 1,
            'services_total' => $appointment->total_prices - $appointment->delivery_fee
        ];
    }

    /**
     * Get commission details
     */
    private function getCommissionDetails($appointment)
    {
        $commission = $this->getAdminCommission();
        $commissionAmount = ($appointment->total_prices * $commission) / 100;
        $providerAmount = $appointment->total_prices - $commissionAmount;

        return [
            'commission_percentage' => $commission,
            'commission_amount' => $commissionAmount,
            'provider_amount' => $providerAmount,
            'total_amount' => $appointment->total_prices
        ];
    }

    /**
     * Get admin commission from settings
     */
    private function getAdminCommission()
    {
        $setting = Setting::where('key', 'commission_of_admin')->first();
        return $setting ? $setting->value : 1.5; // Default 1.5%
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
                                                  ->count(),
            'service_based_appointments' => Appointment::whereHas('providerType.type', function($q) {
                $q->where('booking_type', 'service');
            })->count(),
            'hourly_appointments' => Appointment::whereHas('providerType.type', function($q) {
                $q->where('booking_type', 'hourly')->orWhereNull('booking_type');
            })->count()
        ];
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