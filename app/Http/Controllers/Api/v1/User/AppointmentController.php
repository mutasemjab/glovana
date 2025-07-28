<?php

namespace App\Http\Controllers\Api\v1\User;

use App\Http\Controllers\Admin\FCMController;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\AppointmentService;
use App\Models\ProviderType;
use App\Models\Coupon;
use App\Models\Delivery;
use App\Models\Service;
use App\Models\Setting;
use App\Models\UserAddress;
use App\Models\UserCoupon;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Traits\Responses;
use Illuminate\Support\Facades\DB;

class AppointmentController extends Controller
{
    use Responses;

    /**
     * Display a listing of appointments
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();

            $appointments = Appointment::with([
                'user',
                'address',
                'providerType',
                'providerType.provider',
                'providerType.type',
                'appointmentServices.service'
            ])
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'desc');

            // Filter by appointment status if provided
            if ($request->has('appointment_status') && $request->appointment_status != '') {
                $appointments->where('appointment_status', $request->appointment_status);
            }

            // Filter by payment status if provided
            if ($request->has('payment_status') && $request->payment_status != '') {
                $appointments->where('payment_status', $request->payment_status);
            }

            // Filter by date range if provided
            if ($request->has('from_date') && $request->from_date != '') {
                $appointments->whereDate('date', '>=', $request->from_date);
            }

            if ($request->has('to_date') && $request->to_date != '') {
                $appointments->whereDate('date', '<=', $request->to_date);
            }

            $appointments = $appointments->paginate(10);

            // Transform the data to include status labels and booking info
            $appointments->getCollection()->transform(function ($appointment) {
                $appointment->appointment_status_label = $this->getAppointmentStatusLabel($appointment->appointment_status);
                $appointment->payment_status_label = $this->getPaymentStatusLabel($appointment->payment_status);
                $appointment->is_vip_label = $appointment->providerType->is_vip == 1 ? 'VIP' : 'Regular';
                $appointment->booking_type = $appointment->providerType->type->booking_type ?? 'hourly';
                $appointment->total_customers = $this->getTotalCustomers($appointment);
                return $appointment;
            });

            return $this->success_response(
                'Appointments retrieved successfully',
                $appointments
            );
        } catch (\Exception $e) {
            return $this->error_response(
                'Failed to retrieve appointments',
                ['error' => $e->getMessage()]
            );
        }
    }


    public function updateAppointmentStatus(Request $request, $appointmentId)
    {
        $user = auth()->user();

        if (!$user instanceof \App\Models\User) {
            return $this->error_response('Unauthorized', 'Only users can update appointment status');
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:2,3,4,5,6,7', // Can't set to pending (1)
            'note' => 'nullable|string|max:500',
            'reason_of_cancel' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return $this->error_response('Validation error', $validator->errors());
        }

        $appointment = \App\Models\Appointment::find($appointmentId);

        if (!$appointment) {
            return $this->error_response('Not found', 'Appointment not found');
        }

        // Check if user owns this appointment
        if ($appointment->user_id !== $user->id) {
            return $this->error_response('Unauthorized', 'You can only update your own appointments');
        }

        // Check if appointment is already canceled or completed
        if (in_array($appointment->appointment_status, [4, 5])) {
            return $this->error_response('Invalid operation', 'Cannot update completed or canceled appointment');
        }

        $responseData = [
            'appointment' => null,
            'status_text' => $this->getAppointmentStatusText($request->status),
            'fine_applied' => false,
            'fine_amount' => 0,
            'fine_details' => null
        ];

        // Handle cancellation (status = 5) with fine checking
        if ($request->status == 5) {
            $reason = $request->reason_of_cancel ?? 'Canceled by user';
            
            try {
                // Use the appointment service to handle cancellation with fine logic
                $appointmentService = new AppointmentService();
                $result = $appointmentService->cancelAppointment($appointment, $reason, 'user');
                
                if (!$result) {
                    return $this->error_response('Failed', 'Failed to cancel appointment');
                }

                // Refresh appointment to get updated data
                $appointment->refresh();
                
                // Check if fine was applied
                $latestFine = $appointment->latestFine;
                if ($latestFine) {
                    $responseData['fine_applied'] = true;
                    $responseData['fine_amount'] = $latestFine->amount;
                    $responseData['fine_details'] = [
                        'id' => $latestFine->id,
                        'amount' => $latestFine->amount,
                        'percentage' => $latestFine->percentage,
                        'reason' => $latestFine->reason,
                        'status' => $latestFine->status_text,
                        'applied_at' => $latestFine->applied_at ? $latestFine->applied_at->toISOString() : null
                    ];
                }

                $responseData['appointment'] = $appointment;
                
                // If fine was applied, include updated user balance
                if ($latestFine && $latestFine->status == 2) {
                    $user->refresh();
                    $responseData['updated_balance'] = $user->balance;
                    
                    return $this->success_response('Appointment canceled. A fine has been applied to your account.', $responseData);
                } else if ($latestFine && $latestFine->status == 1) {
                    return $this->success_response('Appointment canceled. A fine is pending review.', $responseData);
                } else {
                    return $this->success_response('Appointment canceled successfully', $responseData);
                }
                
            } catch (\Exception $e) {
                \Log::error('Error canceling appointment: ' . $e->getMessage());
                return $this->error_response('Error', 'Failed to process cancellation');
            }
        }

        // For other status changes (not cancellation)
        $appointment->appointment_status = $request->status;

        if ($request->filled('note')) {
            $appointment->note = $request->note;
        }

        $appointment->save();
        $responseData['appointment'] = $appointment;

        return $this->success_response('Appointment status updated successfully', $responseData);
    }

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
    
   
    /**
     * Store a new appointment (supports both hourly and service-based)
     */
    public function store(Request $request)
    {
        try {
            $user = Auth::user();

            // Get provider type to determine booking type
            $providerType = ProviderType::with('type')->where('id', $request->provider_type_id)->first();

            if (!$providerType) {
                return $this->error_response('Provider type not found', []);
            }

            $bookingType = $providerType->type->booking_type ?? 'hourly';

            // Base validation rules
            $validationRules = [
                'provider_type_id' => 'required|exists:provider_types,id',
                'address_id' => 'nullable|exists:user_addresses,id',
                'date' => 'required|date|after:now',
                'payment_type' => 'required|string|in:cash,visa,wallet',
                'coupon_code' => 'nullable|string|exists:coupons,code',
                'note' => 'nullable|string|max:500'
            ];

            // Add specific validation based on booking type
            if ($bookingType === 'hourly') {
                $validationRules['number_of_hours'] = 'required|numeric|min:1';
            } else {
                $validationRules['services'] = 'required|array|min:1';
                $validationRules['services.*.service_id'] = 'required|exists:services,id';
                $validationRules['services.*.person'] = 'required|integer|min:1';
            }

            $validator = Validator::make($request->all(), $validationRules);

            if ($validator->fails()) {
                return $this->error_response('Validation failed', $validator->errors());
            }

            // Check if provider type is active and available
            if ($providerType->activate != 1 || $providerType->status != 1) {
                return $this->error_response('Provider type is not available', []);
            }

            if ($request->address_id) {
                // Get user address to find delivery area
                $userAddress = UserAddress::find($request->address_id);
                if (!$userAddress) {
                    return $this->error_response('Address not found', []);
                }
                // Get the delivery fee
                $delivery = Delivery::find($userAddress->delivery_id);
                $deliveryFee = $delivery ? $delivery->price : 0;
            } else {
                $deliveryFee = 0;
            }

            // Calculate prices based on booking type
            if ($bookingType === 'hourly') {
                $servicePrice = $this->calculateHourlyPrice($providerType, $request->number_of_hours);
                $services = null;
            } else {
                $result = $this->calculateServicePrice($providerType, $request->services);
                $servicePrice = $result['total_price'];
                $services = $result['services'];
            }

            $totalPrices = $servicePrice + $deliveryFee;

            // Check for conflicting appointments
            if ($this->hasConflictingAppointment($user->id, $request->date)) {
                return $this->error_response('You already have an appointment at this time', []);
            }

            // Handle coupon
            $couponResult = $this->processCoupon($request->coupon_code, $user->id, $totalPrices);
            if ($couponResult['error']) {
                return $this->error_response($couponResult['message'], []);
            }

            $couponDiscount = $couponResult['discount'];
            $couponId = $couponResult['coupon_id'];
            $totalDiscounts = $couponDiscount;
            $finalTotal = $totalPrices - $totalDiscounts;

            // Check wallet balance if payment type is wallet
            if ($request->payment_type === 'wallet' && $user->balance < $finalTotal) {
                return $this->error_response('Insufficient wallet balance', []);
            }

            DB::beginTransaction();

            try {
                // Generate appointment number
                $appointmentNumber = $this->generateAppointmentNumber();

                // Create appointment
                $appointment = Appointment::create([
                    'number' => $appointmentNumber,
                    'appointment_status' => 1, // Pending
                    'delivery_fee' => $deliveryFee,
                    'total_prices' => $finalTotal,
                    'total_discounts' => $totalDiscounts,
                    'coupon_discount' => $couponDiscount,
                    'payment_type' => $request->payment_type,
                    'payment_status' => 2, // Unpaid
                    'date' => $request->date,
                    'note' => $request->note,
                    'user_id' => $user->id,
                    'address_id' => $request->address_id ?? null,
                    'provider_type_id' => $request->provider_type_id,
                ]);

                // Create appointment services if service-based
                if ($bookingType === 'service' && $request->services) {
                    $this->createAppointmentServices($appointment->id, $request->services);
                }

                // Process immediate payment if wallet
                if ($request->payment_type === 'wallet') {
                    $this->processWalletPayment($appointment, $user, $providerType->provider);
                    $appointment->update(['payment_status' => 1]); // Mark as paid
                }

                // Record coupon usage
                if ($couponId) {
                    UserCoupon::create([
                        'user_id' => $user->id,
                        'coupon_id' => $couponId
                    ]);
                }

                DB::commit();

                // Send notification to provider
                $this->sendNewAppointmentNotificationToProvider($appointment, $providerType->provider);

                // Load relationships for response
                $appointment->load([
                    'user:id,name,email,phone',
                    'address',
                    'providerType',
                    'providerType.provider',
                    'appointmentServices.service'
                ]);

                // Add status labels and calculation details
                $appointment->appointment_status_label = $this->getAppointmentStatusLabel($appointment->appointment_status);
                $appointment->payment_status_label = $this->getPaymentStatusLabel($appointment->payment_status);
                $appointment->is_vip_label = $appointment->providerType->is_vip == 1 ? 'VIP' : 'Regular';
                $appointment->booking_type = $bookingType;
                $appointment->total_customers = $this->getTotalCustomers($appointment);


                return $this->success_response('Appointment created successfully', $appointment);
            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }
        } catch (\Exception $e) {
            return $this->error_response('Failed to create appointment', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Calculate hourly price
     */
    private function calculateHourlyPrice($providerType, $hours)
    {
        return $providerType->price_per_hour * $hours;
    }

    
    /**
     * Calculate service-based price from individual person-service combinations
     */
    private function calculateServicePrice($providerType, $requestedServices)
    {
        $totalPrice = 0;
        $services = [];

        // Group services by service_id to count total customers per service
        $serviceGroups = [];
        
        foreach ($requestedServices as $requestedService) {
            $serviceId = $requestedService['service_id'];
            
            // Get service price from provider_services table
            $providerService = DB::table('provider_services')
                ->where('provider_type_id', $providerType->id)
                ->where('service_id', $serviceId)
                ->where('is_active', 1)
                ->first();

            if (!$providerService) {
                throw new \Exception("Service ID {$serviceId} not available for this provider");
            }

            // Add to total price (each person pays the service price)
            $totalPrice += $providerService->price;
            
            // Group for summary (optional, for display purposes)
            if (!isset($serviceGroups[$serviceId])) {
                $serviceGroups[$serviceId] = [
                    'service_id' => $serviceId,
                    'customer_count' => 0,
                    'service_price' => $providerService->price,
                    'total_price' => 0
                ];
            }
            
            $serviceGroups[$serviceId]['customer_count']++;
            $serviceGroups[$serviceId]['total_price'] += $providerService->price;
        }

        return [
            'total_price' => $totalPrice,
            'services' => array_values($serviceGroups) // For backward compatibility if needed
        ];
    }

    /**
     * Create appointment services
     */
   private function createAppointmentServices($appointmentId, $services)
    {
        // Get the appointment to access provider_type_id
        $appointment = Appointment::find($appointmentId);
        
        foreach ($services as $service) {
            // Get service price from provider_services table instead of services table
            $providerService = DB::table('provider_services')
                ->where('provider_type_id', $appointment->provider_type_id)
                ->where('service_id', $service['service_id'])
                ->where('is_active', 1)
                ->first();

            if (!$providerService) {
                throw new \Exception("Service ID {$service['service_id']} not available for this provider");
            }

            AppointmentService::create([
                'appointment_id' => $appointmentId,
                'service_id' => $service['service_id'],
                'person_number' => $service['person'],
                'customer_count' => 1,
                'service_price' => $providerService->price, // Use price from provider_services table
                'total_price' => $providerService->price * 1 // service_price * customer_count
            ]);
        }
    }

    /**
     * Process wallet payment with commission
     */
    private function processWalletPayment($appointment, $user, $provider)
    {
        $commission = $this->getAdminCommission();
        $commissionAmount = ($appointment->total_prices * $commission) / 100;
        $providerAmount = $appointment->total_prices - $commissionAmount;

        // Deduct from user wallet
        $user->decrement('balance', $appointment->total_prices);

        WalletTransaction::create([
            'user_id' => $user->id,
            'amount' => $appointment->total_prices,
            'type_of_transaction' => 2, // withdrawal
            'note' => "Payment for appointment #{$appointment->number}"
        ]);

        // Add to provider wallet
        $provider->increment('balance', $providerAmount);

        WalletTransaction::create([
            'provider_id' => $provider->id,
            'amount' => $providerAmount,
            'type_of_transaction' => 1, // add
            'note' => "Payment from wallet appointment #{$appointment->number}"
        ]);

        // Add commission to admin
        WalletTransaction::create([
            'admin_id' => 1, // Assuming admin ID is 1
            'amount' => $commissionAmount,
            'type_of_transaction' => 1, // add
            'note' => "Commission from wallet appointment #{$appointment->number}"
        ]);
    }

    /**
     * Check for conflicting appointments
     */
    private function hasConflictingAppointment($userId, $date)
    {
        return Appointment::where('user_id', $userId)
            ->where('date', $date)
            ->whereIn('appointment_status', [1, 2, 3]) // Pending, Accepted, OnTheWay
            ->exists();
    }

    /**
     * Process coupon
     */
    private function processCoupon($couponCode, $userId, $totalPrices)
    {
        if (!$couponCode) {
            return ['error' => false, 'discount' => 0, 'coupon_id' => null];
        }

        $coupon = Coupon::where('code', $couponCode)
            ->where('type', 2) // Coupon for provider type
            ->where('expired_at', '>=', now())
            ->first();

        if (!$coupon) {
            return ['error' => true, 'message' => 'Invalid or expired coupon code'];
        }

        // Check if user already used this coupon
        $userCouponUsed = UserCoupon::where('user_id', $userId)
            ->where('coupon_id', $coupon->id)
            ->exists();

        if ($userCouponUsed) {
            return ['error' => true, 'message' => 'You have already used this coupon'];
        }

        // Check minimum total requirement
        if ($totalPrices < $coupon->minimum_total) {
            return ['error' => true, 'message' => "Minimum total of {$coupon->minimum_total} required to use this coupon"];
        }

        return [
            'error' => false,
            'discount' => $coupon->amount,
            'coupon_id' => $coupon->id
        ];
    }

    /**
     * Get total customers for an appointment
     */
    private function getTotalCustomers($appointment)
    {
        if (
            isset($appointment->providerType->type->booking_type) &&
            $appointment->providerType->type->booking_type == 'service'
        ) {
            return $appointment->appointmentServices->sum('customer_count');
        }
        return 1; // Default for hourly appointments
    }

    /**
     * Get admin commission from settings
     */
    private function getAdminCommission()
    {
        $setting = Setting::where('key', 'commission_of_admin')->first();
        return $setting ? $setting->value : 1.5; // Default 1.5%
    }

    private function sendNewAppointmentNotificationToProvider($appointment, $provider)
    {
        try {
            $user = $appointment->user;
            $appointmentDate = \Carbon\Carbon::parse($appointment->date)->format('M d, Y H:i');

            $title = "New Appointment Request";
            $body = "New appointment from {$user->name} scheduled for {$appointmentDate}. Appointment #{$appointment->number}";

            // Send FCM notification to provider
            FCMController::sendMessageToProvider($title, $body, $provider->id);

            \Log::info("New appointment notification sent to provider ID: {$provider->id} for appointment ID: {$appointment->id}");
        } catch (\Exception $e) {
            \Log::error("Failed to send new appointment notification to provider: " . $e->getMessage());
        }
    }

    private function generateAppointmentNumber()
    {
        $lastAppointment = Appointment::orderBy('id', 'desc')->first();
        return $lastAppointment ? $lastAppointment->id + 1 : 1;
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


    public function selectPaymentMethod(Request $request, $appointmentId)
    {
        try {
            $user = Auth::user();

            $validator = Validator::make($request->all(), [
                'payment_type' => 'required|string|in:cash,visa,wallet'
            ]);

            if ($validator->fails()) {
                return $this->error_response('Validation failed', $validator->errors());
            }

            $appointment = Appointment::where('user_id', $user->id)
                ->where('appointment_status', 4) // Must be delivered
                ->where('payment_status', 2) // Must be unpaid
                ->with(['providerType.provider'])
                ->find($appointmentId);

            if (!$appointment) {
                return $this->error_response('Appointment not found or not eligible for payment', []);
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

            return $this->success_response('Payment method selected successfully. Waiting for provider confirmation.', [
                'appointment' => $appointment,
                'payment_type' => $request->payment_type,
                'amount' => $appointment->total_prices,
                'status' => 'pending_provider_confirmation'
            ]);

        } catch (\Exception $e) {
            return $this->error_response('Failed to select payment method', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Get appointments that require payment method selection
     */
    public function getPendingPaymentAppointments(Request $request)
    {
        try {
            $user = Auth::user();

            $appointments = Appointment::with([
                'providerType',
                'providerType.provider',
                'providerType.type',
                'appointmentServices.service'
            ])
            ->where('user_id', $user->id)
            ->where('appointment_status', 4) // Delivered
            ->where('payment_status', 2) // Unpaid
            ->orderBy('updated_at', 'desc')
            ->get();

            // Transform the data
            $appointments->transform(function ($appointment) {
                $appointment->appointment_status_label = $this->getAppointmentStatusLabel($appointment->appointment_status);
                $appointment->payment_status_label = $this->getPaymentStatusLabel($appointment->payment_status);
                $appointment->booking_type = $appointment->providerType->type->booking_type ?? 'hourly';
                $appointment->total_customers = $this->getTotalCustomers($appointment);
                
                // Add payment options info
                $appointment->payment_options = [
                    'can_pay_with_wallet' => auth()->user()->balance >= $appointment->total_prices,
                    'user_wallet_balance' => auth()->user()->balance,
                    'required_amount' => $appointment->total_prices
                ];

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

    /**
     * Send payment confirmation request to provider
     */
    private function sendPaymentConfirmationRequestToProvider($appointment)
    {
        try {
            $title = "Payment Confirmation Required";
            $body = "Customer selected {$appointment->payment_type} payment for appointment #{$appointment->number}. Please confirm payment.";

            FCMController::sendMessageToProvider($title, $body, $appointment->providerType->provider->id);
            \Log::info("Payment confirmation request sent to provider ID: {$appointment->providerType->provider->id}");
        } catch (\Exception $e) {
            \Log::error("Failed to send payment confirmation request to provider: " . $e->getMessage());
        }
    }

}
