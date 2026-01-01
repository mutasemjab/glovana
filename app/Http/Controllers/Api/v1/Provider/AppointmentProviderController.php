<?php

namespace App\Http\Controllers\Api\v1\Provider;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use App\Models\ClassTeacher;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Admin\FCMController; // <-- Import the FCMController here
use App\Models\Appointment;
use App\Models\ParentStudent;
use App\Models\Setting;
use App\Models\WalletTransaction;
use App\Traits\Responses;
use Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class AppointmentProviderController extends Controller
{
    use Responses;
    

    public function paymentReport(Request $request)
    {
        $provider = auth()->user();

        if (!$provider instanceof \App\Models\Provider) {
            return $this->error_response('Unauthorized', 'Only providers can view reports');
        }

        $validator = Validator::make($request->all(), [
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'payment_type' => 'nullable|in:cash,visa,wallet',
            'appointment_status' => 'nullable|in:1,2,3,4,5',
            'payment_status' => 'nullable|in:1,2',
        ]);

        if ($validator->fails()) {
            return $this->error_response('Validation error', $validator->errors());
        }

        $appointments = Appointment::whereHas('providerType', function ($q) use ($provider) {
            $q->where('provider_id', $provider->id);
        })
        ->when($request->filled('date_from'), fn($q) => $q->whereDate('date', '>=', $request->date_from))
        ->when($request->filled('date_to'), fn($q) => $q->whereDate('date', '<=', $request->date_to))
        ->when($request->filled('payment_type'), fn($q) => $q->where('payment_type', $request->payment_type))
        ->when($request->filled('appointment_status'), fn($q) => $q->where('appointment_status', $request->appointment_status))
        ->when($request->filled('payment_status'), fn($q) => $q->where('payment_status', $request->payment_status))
        ->with('providerType.provider')
        ->get();

        $commissionRate = $this->getAdminCommission();

        $report = [
            'total_appointments' => $appointments->count(),
            'total_amount' => 0,
            'total_commission' => 0,
            'total_provider_earnings' => 0,
            'appointments' => [],
        ];

        foreach ($appointments as $appointment) {
            $commission = ($appointment->total_prices * $commissionRate) / 100;
            $providerEarnings = $appointment->total_prices - $commission;

            $report['total_amount'] += $appointment->total_prices;
            $report['total_commission'] += $commission;
            $report['total_provider_earnings'] += $providerEarnings;

            $report['appointments'][] = [
                'id' => $appointment->id,
                'number' => $appointment->number,
                'date' => $appointment->date,
                'status' => $this->getAppointmentStatusText($appointment->appointment_status),
                'payment_type' => $appointment->payment_type,
                'payment_status' => $appointment->payment_status == 1 ? 'Paid' : 'Unpaid',
                'total' => $appointment->total_prices,
                'commission' => round($commission, 2),
                'provider_earnings' => round($providerEarnings, 2),
            ];
        }

        return $this->success_response('Payment report generated', $report);
    }

     public function getPendingPaymentConfirmations(Request $request)
    {
        try {
            $provider = auth()->user();

            if (!$provider instanceof \App\Models\Provider) {
                return $this->error_response('Unauthorized', 'Only providers can view appointments');
            }

                  $appointments = Appointment::whereHas('providerType', function ($q) use ($provider) {
                    $q->where('provider_id', $provider->id);
                })->with([
                    'user:id,name,phone,email,photo',
                    'providerType',
                    'providerType.provider',
                    'providerType.type',
                    'appointmentServices.service'
                ])->where('appointment_status', 4) // Delivered
                    ->where('payment_status', 2) // Unpaid
                    ->orderBy('updated_at', 'desc')
                    ->get();


            // Transform the data
            $appointments->transform(function ($appointment) {
                $appointment->appointment_status_label = $this->getAppointmentStatusText($appointment->appointment_status);
                $appointment->booking_type = $appointment->providerType->type->booking_type ?? 'hourly';                

                return $appointment;
            });

            return $this->success_response('Pending payment appointments retrieved successfully', [
                'appointments' => $appointments,
                'count' => $appointments->count()
            ]);

        } catch (\Exception $e) {
            return $this->error_response('Failed to retrieve pending payment appointments', ['error' => $e->getMessage()]);
        }
    }

    public function getProviderAppointments(Request $request)
    {
        $provider = auth()->user();

        if (!$provider instanceof \App\Models\Provider) {
            return $this->error_response('Unauthorized', 'Only providers can view appointments');
        }

        $validator = Validator::make($request->all(), [
            'status' => 'nullable|in:1,2,3,4,5,6,7', // Filter by status
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'per_page' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1'
        ]);

        if ($validator->fails()) {
            return $this->error_response('Validation error', $validator->errors());
        }

        $query = \App\Models\Appointment::whereHas('providerType', function ($q) use ($provider) {
            $q->where('provider_id', $provider->id);
        })->with([
            'user:id,name,phone,email,photo',
            'address',
            'providerType',
            'providerType.type',
            'appointmentServices.service'
        ]);

        // Filter by status if provided
        if ($request->filled('status')) {
            $query->where('appointment_status', $request->status);
        }

        // Filter by date range if provided
        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        // Order by date (newest first)
        $query->orderBy('date', 'desc');

        $perPage = $request->get('per_page', 15);
        $appointments = $query->paginate($perPage);

        // Add status text and booking type info
        $appointments->getCollection()->transform(function ($appointment) {
            $appointment->status_text = $this->getAppointmentStatusText($appointment->appointment_status);
            $appointment->payment_status_text = $appointment->payment_status == 1 ? 'Paid' : 'Unpaid';
            $appointment->booking_type = $appointment->providerType->type->booking_type ?? 'hourly';
            $appointment->total_customers = $this->getTotalCustomers($appointment);
            $appointment->can_finish = $appointment->appointment_status == 3; // Can finish if "On The Way"
            $appointment->requires_payment_confirmation = ($appointment->appointment_status == 4 && $appointment->payment_status == 2);
            return $appointment;
        });

        return $this->success_response('Appointments retrieved successfully', [
            'appointments' => $appointments,
        ]);
    }

    public function getAppointmentDetails(Request $request, $appointmentId)
    {
        $provider = auth()->user();

        if (!$provider instanceof \App\Models\Provider) {
            return $this->error_response('Unauthorized', 'Only providers can view appointment details');
        }

        $appointment = \App\Models\Appointment::whereHas('providerType', function ($q) use ($provider) {
            $q->where('provider_id', $provider->id);
        })->with([
            'user:id,name,phone,email,photo,balance',
            'address',
            'providerType',
            'providerType.type',
            'providerType.provider:id,name_of_manager,phone',
            'appointmentServices.service'
        ])->find($appointmentId);

        if (!$appointment) {
            return $this->error_response('Not found', 'Appointment not found');
        }

        $appointment->status_text = $this->getAppointmentStatusText($appointment->appointment_status);
        $appointment->payment_status_text = $appointment->payment_status == 1 ? 'Paid' : 'Unpaid';
        $appointment->booking_type = $appointment->providerType->type->booking_type ?? 'hourly';
        $appointment->total_customers = $this->getTotalCustomers($appointment);
        $appointment->can_finish = $appointment->appointment_status == 3;
        $appointment->requires_payment_confirmation = ($appointment->appointment_status == 4 && $appointment->payment_status == 2);
        
        // Add services summary for service-based appointments
        if ($appointment->booking_type == 'service') {
            $appointment->services_summary = $this->getServicesSummary($appointment);
        }

        // Add payment options info
        $appointment->payment_options = [
            'can_pay_with_wallet' => $appointment->user->balance >= $appointment->total_prices,
            'user_wallet_balance' => $appointment->user->balance,
            'required_amount' => $appointment->total_prices
        ];

        return $this->success_response('Appointment details retrieved successfully', [
            'appointment' => $appointment
        ]);
    }

    public function updateAppointmentStatus(Request $request, $appointmentId)
    {
        $provider = auth()->user();

        if (!$provider instanceof \App\Models\Provider) {
            return $this->error_response('Unauthorized', 'Only providers can update appointment status');
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:2,3,4,5,6,7', // Can't set to pending (1)
            'note' => 'nullable|string|max:500',
            'reason_of_cancel' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return $this->error_response('Validation error', $validator->errors());
        }

        $appointment = \App\Models\Appointment::whereHas('providerType', function ($q) use ($provider) {
            $q->where('provider_id', $provider->id);
        })->with(['user', 'providerType.provider'])->find($appointmentId);

        if (!$appointment) {
            return $this->error_response('Not found', 'Appointment not found');
        }

        // Check if status transition is valid
        $currentStatus = $appointment->appointment_status;
        $newStatus = $request->status;

       
        // Special handling for completing appointment (status 4)
        if ($newStatus == 4) {
            // Mark as delivered but keep payment status as unpaid
            // This will trigger payment selection process
            $appointment->appointment_status = 4;
            
            if ($request->filled('note')) {
                $appointment->note = $request->note;
            }
            
            $appointment->save();

            // Send notification to user to select payment method
            $this->sendPaymentSelectionNotificationToUser($appointment);

            return $this->success_response('Appointment completed. Waiting for payment confirmation.', [
                'appointment' => $appointment,
                'status_text' => $this->getAppointmentStatusText($newStatus),
                'requires_payment_confirmation' => true,
                'message' => 'Service completed. Customer will select payment method.'
            ]);
        }

        // For other status changes
        $appointment->appointment_status = $newStatus;

        if ($request->filled('note')) {
            $appointment->note = $request->note;
        }
        if ($request->filled('reason_of_cancel')) {
            $appointment->reason_of_cancel = $request->reason_of_cancel;
        }

        $appointment->save();

        $this->sendAppointmentStatusNotificationToUser($appointment, $currentStatus, $newStatus);

        return $this->success_response('Appointment status updated successfully', [
            'appointment' => $appointment,
            'status_text' => $this->getAppointmentStatusText($newStatus)
        ]);
    }

    /**
     * User selects payment method after service completion
     */
    public function selectPaymentMethod(Request $request, $appointmentId)
    {
        $user = auth()->user();

        $validator = Validator::make($request->all(), [
            'payment_type' => 'required|string|in:cash,visa,wallet'
        ]);

        if ($validator->fails()) {
            return $this->error_response('Validation error', $validator->errors());
        }

        $appointment = \App\Models\Appointment::where('user_id', $user->id)
            ->where('appointment_status', 4) // Must be delivered
            ->where('payment_status', 2) // Must be unpaid
            ->with(['user', 'providerType.provider'])
            ->find($appointmentId);

        if (!$appointment) {
            return $this->error_response('Not found', 'Appointment not found or not eligible for payment');
        }

        // Check wallet balance if payment type is wallet
        if ($request->payment_type === 'wallet' && $user->balance < $appointment->total_prices) {
            return $this->error_response('Insufficient wallet balance', [
                'required_amount' => $appointment->total_prices,
                'current_balance' => $user->balance
            ]);
        }

        // Update payment type
        $appointment->payment_type = $request->payment_type;
        $appointment->save();

        // Send notification to provider for payment confirmation
        $this->sendPaymentConfirmationRequestToProvider($appointment);

        return $this->success_response('Payment method selected. Waiting for provider confirmation.', [
            'appointment' => $appointment,
            'payment_type' => $request->payment_type,
            'amount' => $appointment->total_prices
        ]);
    }

    /**
     * Provider confirms payment after user selects payment method
     */
    public function confirmPayment(Request $request, $appointmentId)
    {
        $provider = auth()->user();

        if (!$provider instanceof \App\Models\Provider) {
            return $this->error_response('Unauthorized', 'Only providers can confirm payment');
        }

        $validator = Validator::make($request->all(), [
            'payment_confirmed' => 'required|boolean'
        ]);

        if ($validator->fails()) {
            return $this->error_response('Validation error', $validator->errors());
        }

        $appointment = \App\Models\Appointment::whereHas('providerType', function ($q) use ($provider) {
            $q->where('provider_id', $provider->id);
        })->where('appointment_status', 4) // Must be delivered
            ->where('payment_status', 2) // Must be unpaid
            ->with(['user', 'providerType.provider'])
            ->find($appointmentId);

        if (!$appointment) {
            return $this->error_response('Not found', 'Appointment not found or not eligible for payment confirmation');
        }

        if (!$request->payment_confirmed) {
            return $this->error_response('Payment not confirmed', 'Provider must confirm payment to proceed');
        }

        try {
            DB::beginTransaction();

            // Process payment based on selected payment type
            $this->processPaymentByType($appointment);

            // Mark appointment as paid
            $appointment->payment_status = 1;
            $appointment->save();

            DB::commit();

            // Send payment confirmation to user
            $this->sendPaymentConfirmationToUser($appointment);

            return $this->success_response('Payment confirmed and processed successfully', [
                'appointment' => $appointment,
                'payment_type' => $appointment->payment_type,
                'amount_processed' => $appointment->total_prices
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return $this->error_response('Payment processing failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Process payment based on type with commission handling
     */
    private function processPaymentByType($appointment)
    {
        $commission = $this->getAdminCommission();
        $commissionAmount = ($appointment->total_prices * $commission) / 100;
        $providerAmount = $appointment->total_prices - $commissionAmount;
        $provider = $appointment->providerType->provider;
        $user = $appointment->user;

        switch ($appointment->payment_type) {
            case 'cash':
                $this->processCashPayment($appointment, $provider, $commissionAmount);
                break;
                
            case 'visa':
                $this->processVisaPayment($appointment, $provider, $commissionAmount, $providerAmount);
                break;
                
            case 'wallet':
                $this->processWalletPayment($appointment, $user, $provider, $commissionAmount, $providerAmount);
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
        
        WalletTransaction::create([
            'provider_id' => $provider->id,
            'admin_id' => 1,
            'amount' => $commissionAmount,
            'type_of_transaction' => 2, // withdrawal
            'note' => "Commission deduction for cash appointment #{$appointment->number}"
        ]);
    }

    /**
     * Process visa payment
     */
    private function processVisaPayment($appointment, $provider, $commissionAmount, $providerAmount)
    {
        // Add provider amount to provider wallet
        $provider->increment('balance', $providerAmount);
        
        WalletTransaction::create([
            'provider_id' => $provider->id,
             'admin_id' => 1,
            'amount' => $providerAmount,
            'type_of_transaction' => 1, // add
            'note' => "Payment from visa appointment #{$appointment->number}"
        ]);

          // deduct commission from provider wallet
        WalletTransaction::create([
            'provider_id' => $provider->id,
             'admin_id' => 1,
            'amount' => $commissionAmount,
            'type_of_transaction' => 2, // withdrawal
            'note' => "Commission from visa appointment #{$appointment->number}"
        ]);
    }

    /**
     * Process wallet payment
     */
    private function processWalletPayment($appointment, $user, $provider, $commissionAmount, $providerAmount)
    {
        // Deduct total from user wallet
        $user->decrement('balance', $appointment->total_prices);
        
        WalletTransaction::create([
            'user_id' => $user->id,
             'admin_id' => 1,
            'amount' => $appointment->total_prices,
            'type_of_transaction' => 2, // withdrawal
            'note' => "Payment for appointment #{$appointment->number}"
        ]);

        // Add provider amount to provider wallet
        $provider->increment('balance', $providerAmount);
        
        WalletTransaction::create([
            'provider_id' => $provider->id,
             'admin_id' => 1,
            'amount' => $providerAmount,
            'type_of_transaction' => 1, // add
            'note' => "Payment from wallet appointment #{$appointment->number}"
        ]);

        // deduct commission from provider wallet
        WalletTransaction::create([
            'provider_id' => $provider->id,
             'admin_id' => 1,
            'amount' => $commissionAmount,
            'type_of_transaction' => 2, // withdrawal
            'note' => "Commission from wallet appointment #{$appointment->number}"
        ]);
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
     * Get services summary for service-based appointments including individual customer services
     */
    private function getServicesSummary($appointment)
    {
        if (isset($appointment->providerType->type->booking_type) && 
            $appointment->providerType->type->booking_type == 'service') {
            
            // Get aggregated services (existing functionality)
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

            // Get individual customer services grouped by person
            $customerServices = $appointment->appointmentServices
                ->groupBy('person_number')
                ->map(function ($services, $personNumber) {
                    return [
                        'person_number' => $personNumber,
                        'total_services' => $services->count(),
                        'total_amount' => $services->sum('service_price'),
                        'services' => $services->map(function ($service) {
                            return [
                                'service_id' => $service->service_id,
                                'service_name' => app()->getLocale() == 'ar' ? 
                                    $service->service->name_ar : 
                                    $service->service->name_en,
                                'service_price' => $service->service_price
                            ];
                        })->toArray()
                    ];
                })
                ->values()
                ->toArray();

            return [
                'services' => $services,
                'total_services' => $services->count(),
                'total_customers' => $services->sum('customer_count'),
                'services_total' => $services->sum('total_price'),
                'customer_services' => $customerServices // Individual customer breakdown
            ];
        }
        
        return null;
    }

    /**
     * Get admin commission from settings
     */
    private function getAdminCommission()
    {
        $setting = Setting::where('key', 'commission_of_admin')->first();
        return $setting ? $setting->value : 1.5; // Default 1.5%
    }

    // Notification Methods
    private function sendPaymentSelectionNotificationToUser($appointment)
    {
        try {
            $title = "Service Completed - Select Payment Method";
            $body = "Your service has been completed. Please select your payment method for appointment #{$appointment->number}";

            FCMController::sendMessageToUser($title, $body, $appointment->user_id);
            \Log::info("Payment selection notification sent to user ID: {$appointment->user_id}");
        } catch (\Exception $e) {
            \Log::error("Failed to send payment selection notification: " . $e->getMessage());
        }
    }

    private function sendPaymentConfirmationRequestToProvider($appointment)
    {
        try {
            $title = "Payment Confirmation Required";
            $body = "Customer selected {$appointment->payment_type} payment for appointment #{$appointment->number}. Please confirm payment.";

            FCMController::sendMessageToProvider($title, $body, $appointment->providerType->provider->id);
            \Log::info("Payment confirmation request sent to provider");
        } catch (\Exception $e) {
            \Log::error("Failed to send payment confirmation request: " . $e->getMessage());
        }
    }

    private function sendPaymentConfirmationToUser($appointment)
    {
        try {
            $title = "Payment Confirmed";
            $body = "Your payment has been confirmed for appointment #{$appointment->number}. Thank you!";

            FCMController::sendMessageToUser($title, $body, $appointment->user_id);
            \Log::info("Payment confirmation sent to user");
        } catch (\Exception $e) {
            \Log::error("Failed to send payment confirmation: " . $e->getMessage());
        }
    }

    private function sendAppointmentStatusNotificationToUser($appointment, $oldStatus, $newStatus)
    {
        try {
            $statusMessages = [
                2 => 'Your appointment has been accepted by the provider',
                3 => 'Your provider is on the way',
                4 => 'Your appointment has been completed',
                5 => 'Your appointment has been cancelled'
            ];

            $title = "Appointment Status Update";
            $body = $statusMessages[$newStatus] ?? "Your appointment status has been updated";
            $body .= " - Appointment #{$appointment->number}";
            
            // Add cancellation reason if status is 5 and reason exists
            if ($newStatus == 5 && !empty($appointment->reason_of_cancel)) {
                $body .= ". Reason: {$appointment->reason_of_cancel}";
            }

            FCMController::sendMessageToUser($title, $body, $appointment->user_id);
            \Log::info("Appointment status notification sent to user ID: {$appointment->user_id}");
        } catch (\Exception $e) {
            \Log::error("Failed to send appointment status notification to user: " . $e->getMessage());
        }
    }
    // Helper Methods
    private function getAppointmentStatusText($status)
    {
        $statuses = [
            1 => 'Pending',
            2 => 'Accepted',
            3 => 'On The Way',
            4 => 'Delivered',
            5 => 'Canceled',
            6 => 'Start work',
            7 => 'User arrived to provider',
        ];

        return $statuses[$status] ?? 'Unknown';
    }

    private function isValidStatusTransition($currentStatus, $newStatus)
    {
        $validTransitions = [
            1 => [2, 5], // From Pending: can go to Accepted or Canceled
            2 => [3, 5], // From Accepted: can go to On The Way or Canceled
            3 => [4, 5], // From On The Way: can go to Delivered or Canceled
            4 => [],     // From Delivered: no transitions allowed
            5 => []      // From Canceled: no transitions allowed
        ];

        return in_array($newStatus, $validTransitions[$currentStatus] ?? []);
    }
}
