<?php

namespace App\Http\Controllers\Api\v1\User;

use App\Http\Controllers\Admin\FCMController;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\AppointmentService;
use App\Models\ProviderType;
use App\Models\Coupon;
use App\Models\Delivery;
use App\Models\Discount;
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
use App\Services\AppointmentService as AppointmentServiceClass;

class AppointmentController extends Controller
{
    use Responses;

    private const AUTO_CANCEL_TIMEOUT_MINUTES = 10;


    public function enableCancelRating(Request $request)
    {
        $request->validate([
            'appointment_id' => 'required|exists:appointments,id'
        ]);

        $user = Auth::guard('user-api')->user();

        $appointment = Appointment::where('id', $request->appointment_id)
            ->where('user_id', $user->id) // security check
            ->first();

        if (!$appointment) {
            return $this->error_response('Appointment not found',[]);
        }

        $appointment->update([
            'cancel_rating' => 1
        ]);

        return $this->success_response('Cancel rating enabled successfully', [
            'appointment_id' => $appointment->id,
            'cancel_rating' => $appointment->cancel_rating
        ]);
    }

    public function index(Request $request)
    {
        try {
            $user = Auth::user();

            // Validation matching provider structure
            $validator = Validator::make($request->all(), [
                'status' => 'nullable|in:1,2,3,4,5', // Filter by status
                'date_from' => 'nullable|date',
                'date_to' => 'nullable|date|after_or_equal:date_from',
                'payment_status' => 'nullable|in:1,2', // 1 = Paid, 2 = Unpaid
                'per_page' => 'nullable|integer|min:1|max:100',
                'page' => 'nullable|integer|min:1'
            ]);

            if ($validator->fails()) {
                return $this->error_response('Validation error', $validator->errors());
            }

            // Build query with relationships including discount
            $query = Appointment::with([
                'user:id,name,phone,email,photo',
                'address',
                'providerType',
                'providerType.images',
                'providerType.provider:id,name_of_manager,phone,photo_of_manager',
                'providerType.type',
                'appointmentServices.service',
                'discount' // Include discount relationship
            ])->where('user_id', $user->id);

            // Filter by appointment status if provided
            if ($request->filled('status')) {
                $query->where('appointment_status', $request->status);
            }

            // Filter by payment status if provided
            if ($request->filled('payment_status')) {
                $query->where('payment_status', $request->payment_status);
            }

            // Filter by date range if provided
            if ($request->filled('date_from')) {
                $query->whereDate('date', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('date', '<=', $request->date_to);
            }

            // Order by date (newest first) - matching provider structure
            $query->orderBy('date', 'desc');

            // Pagination
            $perPage = $request->get('per_page', 15);
            $appointments = $query->paginate($perPage);

            // Transform the data to include status labels and discount info
            $appointments->getCollection()->transform(function ($appointment) {

                // Status labels
                $appointment->status_text = $this->getAppointmentStatusText($appointment->appointment_status);
                $appointment->payment_status_text = $appointment->payment_status == 1 ? 'Paid' : 'Unpaid';

                // Provider info
                $appointment->is_vip_label = $appointment->providerType->is_vip == 1 ? 'VIP' : 'Regular';
                $appointment->booking_type = $appointment->providerType->type->booking_type ?? 'service';

                // Customer and service info
                $appointment->total_customers = $this->getTotalCustomers($appointment);

                // Enhanced pricing info with discount details
                $hasDiscountNew = isset($appointment->has_discount) && $appointment->has_discount == 1;
                $hasDiscountOld = $appointment->total_discounts > 0;
                $actualHasDiscount = $hasDiscountNew || $hasDiscountOld;

                $appointment->pricing_info = [
                    'original_total' => $appointment->original_total_price ??
                        ($appointment->total_prices + $appointment->total_discounts),
                    'final_total' => $appointment->total_prices,
                    'delivery_fee' => $appointment->delivery_fee,
                    'coupon_discount' => $appointment->coupon_discount,
                    'total_discounts' => $appointment->total_discounts,
                    'has_discount' => $actualHasDiscount,
                    'discount_info' => $actualHasDiscount ? [
                        'discount_name' => $appointment->discount?->name ?? 'Special Discount',
                        'discount_percentage' => $appointment->discount_percentage ??
                            ($appointment->total_discounts > 0 ?
                                round(($appointment->total_discounts / ($appointment->total_prices + $appointment->total_discounts)) * 100, 2) : 0),
                        'amount_saved' => $appointment->discount_amount ?? $appointment->total_discounts,
                        'savings_text' => $appointment->discount_amount > 0 ?
                            "You saved {$appointment->discount_amount}!" : ($appointment->total_discounts > 0 ? "You saved {$appointment->total_discounts}!" : null)
                    ] : null
                ];

                // User-specific flags
                $appointment->can_cancel = in_array($appointment->appointment_status, [1, 2]); // Can cancel if Pending or Accepted
                $appointment->can_rate = ($appointment->appointment_status == 4 && $appointment->payment_status == 1); // Can rate if delivered and paid

                // Add services summary for service-based appointments with discount info
                if ($appointment->booking_type == 'service') {
                    $appointment->services_summary = $this->getServicesSummaryWithDiscount($appointment);
                }


                return $appointment;
            });

            return $this->success_response('Appointments retrieved successfully', [
                'appointments' => $appointments,
            ]);
        } catch (\Exception $e) {
            return $this->error_response(
                'Failed to retrieve appointments',
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * FIXED: Store method now supports both 'service' and 'hourly' booking types
     * 
     * Changes made:
     * 1. Moved provider type fetch before validation
     * 2. Dynamic validation rules based on booking_type
     * 3. Conditional processing for service vs hourly bookings
     */

    public function store(Request $request)
    {
        try {
            $user = auth()->user();

            if (empty($user->phone)) {
                return $this->error_response(
                    'Phone number required',
                    [
                        'message' => 'You must add a phone number to your profile before continuing.'
                    ]
                );
            }

            // First, get provider type to determine booking type
            $providerType = ProviderType::with(['type', 'provider'])->find($request->provider_type_id);

            if (!$providerType) {
                return $this->error_response('Invalid provider type', null);
            }

            $bookingType = $providerType->type->booking_type;

            // ===== NEW: Check for concurrent booking limit (only for service type) =====
            if ($bookingType === 'service' && !is_null($providerType->number_of_work)) {
                $requestedDate = $request->date;

                // Count existing appointments for the same provider_type on the same date
                // Only count appointments that are NOT canceled (status != 5)
                $existingAppointmentsCount = Appointment::where('provider_type_id', $request->provider_type_id)
                    ->whereDate('date', $requestedDate)
                    ->whereNotIn('appointment_status', [5]) // Exclude canceled appointments
                    ->count();

                // Check if limit is reached
                if ($existingAppointmentsCount >= $providerType->number_of_work) {
                    return $this->error_response(
                        'Provider is fully booked for this date',
                        [
                            'message' => 'This provider has reached the maximum number of concurrent bookings for the selected date.',
                            'max_bookings' => $providerType->number_of_work,
                            'current_bookings' => $existingAppointmentsCount,
                            'requested_date' => $requestedDate,
                            'suggestion' => 'Please choose a different date or time.'
                        ]
                    );
                }
            }
            // ===== END OF NEW CODE =====

            // Dynamic validation based on booking type
            $rules = [
                'provider_type_id' => 'required|exists:provider_types,id',
                'date' => 'required|date|after:today',
                'address_id' => 'required|exists:user_addresses,id',
                'delivery_fee' => 'nullable',
                'note' => 'nullable|string|max:1000',
                'payment_type' => 'required|in:cash,visa,wallet',
                'coupon_code' => 'nullable|string'

            ];

            // Add booking type specific validation
            if ($bookingType === 'service') {
                $rules['services'] = 'required|array';
                $rules['services.*.service_id'] = 'required|exists:services,id';
                $rules['services.*.customer_count'] = 'required|integer|min:1';
                $rules['services.*.person_number'] = 'nullable|integer|min:1';
            } elseif ($bookingType === 'hourly') {
                $rules['number_of_hours'] = 'required|integer|min:1';
            }

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return $this->error_response('Validation error', $validator->errors());
            }

            DB::beginTransaction();

            try {
                // Calculate pricing based on booking type
                if ($bookingType === 'service') {
                    $pricingData = $this->calculateAppointmentPricing($providerType, $request);
                } else {
                    // For hourly bookings - calculate based on hourly rate
                    $pricingData = $this->calculateHourlyPricing($providerType, $request);
                }

                $couponDiscount = 0;
                $couponId = null;

                // Apply coupon if provided
                if ($request->coupon_code) {
                    $couponService = new \App\Services\CouponService();
                    $couponResult = $couponService->validateAppointmentCoupon(
                        $request->coupon_code,
                        $user->id,
                        $pricingData['final_total']
                    );

                    if (!$couponResult['success']) {
                        DB::rollback();
                        return $this->error_response($couponResult['message'], []);
                    }

                    $couponDiscount = $couponResult['discount'];
                    $couponId = $couponResult['coupon']->id;
                }

                // Adjust final total with coupon
                $finalTotal = $pricingData['final_total'] - $couponDiscount;


                if ($request->payment_type === 'wallet') {
                    if ($user->balance < $pricingData['final_total']) {
                        DB::rollback();
                        return $this->error_response('Insufficient wallet balance', [
                            'required_amount' => $pricingData['final_total'],
                            'current_balance' => $user->balance,
                            'message' => 'Your wallet balance is insufficient. Please add funds or choose a different payment method.'
                        ]);
                    }
                }

                // Generate appointment number
                $appointmentNumber = $this->generateAppointmentNumber();

                // Create appointment with existing + new discount fields
                $appointment = Appointment::create([
                    'user_id' => $user->id,
                    'provider_type_id' => $request->provider_type_id,
                    'date' => $request->date,
                    'address_id' => $request->address_id,
                    'note' => $request->note,
                    'payment_type' => $request->payment_type,
                    'number' => $appointmentNumber,

                    // Pricing fields
                    'delivery_fee' => $request->delivery_fee ?? 0,
                    'total_prices' => $finalTotal,
                    'total_discounts' => $pricingData['discount_amount'],
                    'coupon_discount' => $couponDiscount,
                    'coupon_id' => $couponId,

                    // New discount fields
                    'original_total_price' => $pricingData['original_total'],
                    'discount_id' => $pricingData['discount_id'],
                    'discount_percentage' => $pricingData['discount_percentage'],
                    'discount_amount' => $pricingData['discount_amount'],
                    'has_discount' => $pricingData['has_discount'] ? 1 : 2,

                    // Default values
                    'appointment_status' => 1,
                    'payment_status' => 2,
                    'fine_amount' => 0,
                    'fine_applied' => 2
                ]);

                // Create appointment services only for service-based bookings
                if ($bookingType === 'service') {
                    foreach ($request->services as $serviceData) {
                        $serviceInfo = $this->getServicePricingInfoForBooking($providerType->id, $serviceData['service_id']);

                        AppointmentService::create([
                            'appointment_id' => $appointment->id,
                            'service_id' => $serviceData['service_id'],
                            'customer_count' => $serviceData['customer_count'],
                            'person_number' => $serviceData['person_number'] ?? 1,

                            // Existing pricing fields
                            'service_price' => $serviceInfo['current_price'],
                            'total_price' => $serviceInfo['current_price'] * $serviceData['customer_count'],

                            // New discount fields for services
                            'original_service_price' => $serviceInfo['original_price'],
                            'service_discount_percentage' => $serviceInfo['discount_percentage'] ?? 0,
                            'service_discount_amount' => $serviceInfo['discount_amount_per_service'] ?? 0,
                            'has_service_discount' => $serviceInfo['has_discount'] ? 1 : 2,
                        ]);
                    }

                    // ===== NEW: Schedule auto-cancellation for service-based appointments =====
                    $this->scheduleAutoCancellation($appointment->id, $user->id, $providerType->provider->id);
                    // ===== END OF NEW CODE =====
                }
                // For hourly bookings, you might want to store number_of_hours in a separate table or field
                // This depends on your database schema

                if ($couponId) {
                    $couponService->markAsUsed($couponId, $user->id);
                }
                $this->sendNewAppointmentNotificationToProvider($appointment, $user, $providerType);

                DB::commit();

                // Load the complete appointment data
                $appointment->load(['appointmentServices.service', 'address', 'providerType.provider', 'discount']);

                return $this->success_response('Appointment created successfully', [
                    'appointment' => $appointment,
                    'pricing_breakdown' => [
                        'original_total' => $pricingData['original_total'],
                        'discount_applied' => $pricingData['has_discount'],
                        'discount_name' => $pricingData['discount_name'],
                        'discount_percentage' => $pricingData['discount_percentage'],
                        'amount_saved' => $pricingData['discount_amount'],
                        'final_total' => $pricingData['final_total'],
                        'savings_message' => $pricingData['discount_amount'] > 0 ?
                            "🎉 You saved {$pricingData['discount_amount']} with this booking!" : null
                    ]
                ]);
            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }
        } catch (\Exception $e) {
            return $this->error_response('Failed to create appointment', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Schedule auto-cancellation for appointment if not accepted within timeout
     */
    private function scheduleAutoCancellation($appointmentId, $userId, $providerId)
    {
        // Dispatch the job with delay
        \App\Jobs\AutoCancelAppointment::dispatch($appointmentId, $userId, $providerId, self::AUTO_CANCEL_TIMEOUT_MINUTES)
            ->delay(now()->addMinutes(self::AUTO_CANCEL_TIMEOUT_MINUTES));
        
        \Log::info("Auto-cancel job scheduled for appointment #{$appointmentId} - will execute in " . self::AUTO_CANCEL_TIMEOUT_MINUTES . " minute(s)");
    }

    /**
     * NEW METHOD: Calculate pricing for hourly bookings
     */
    private function calculateHourlyPricing($providerType, $request)
    {
        $numberOfHours = $request->number_of_hours;

        // Get the hourly rate from provider_type
        $originalHourlyRate = $providerType->price_per_hour ?? 0;
        $currentHourlyRate = $originalHourlyRate;

        $discountId = null;
        $discountPercentage = 0;
        $discountName = null;
        $hasDiscount = false;

        // Check for active hourly discount
        $activeDiscount = Discount::where('provider_type_id', $providerType->id)
            ->where('discount_type', 'hourly')
            ->where('is_active', 1)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->first();

        if ($activeDiscount) {
            $hasDiscount = true;
            $discountId = $activeDiscount->id;
            $discountPercentage = $activeDiscount->percentage;
            $discountName = $activeDiscount->name;

            // Apply discount to hourly rate
            $discountAmount = ($originalHourlyRate * $activeDiscount->percentage) / 100;
            $currentHourlyRate = $originalHourlyRate - $discountAmount;
        }

        $originalTotal = $originalHourlyRate * $numberOfHours;
        $finalTotal = $currentHourlyRate * $numberOfHours;
        $totalDiscountAmount = $originalTotal - $finalTotal;

        return [
            'original_total' => $originalTotal,
            'final_total' => $finalTotal,
            'discount_amount' => $totalDiscountAmount,
            'discount_id' => $discountId,
            'discount_percentage' => $discountPercentage,
            'discount_name' => $discountName,
            'has_discount' => $hasDiscount,
        ];
    }

    /**
     * FIXED: Update method now supports both booking types
     */
    public function update(Request $request, $appointmentId)
    {
        try {
            $user = auth()->user();

            // Find the appointment
            $appointment = Appointment::where('user_id', $user->id)->find($appointmentId);

            if (!$appointment) {
                return $this->error_response('Not found', 'Appointment not found or unauthorized');
            }

            // Check if appointment can be edited (only pending or accepted appointments)
            if (!in_array($appointment->appointment_status, [1, 2])) {
                return $this->error_response('Invalid operation', 'Cannot edit appointment in current status');
            }

            // Get provider type to determine booking type
            $providerType = ProviderType::with(['type', 'provider'])->find($appointment->provider_type_id);
            $bookingType = $providerType->type->booking_type;

            // Dynamic validation based on booking type
            $rules = [
                'date' => 'nullable|date|after:today',
                'address_id' => 'nullable|exists:user_addresses,id',
                'note' => 'nullable|string|max:1000',
                'payment_type' => 'nullable|in:cash,visa,wallet'
            ];

            if ($bookingType === 'service') {
                $rules['services'] = 'nullable|array';
                $rules['services.*.service_id'] = 'required_with:services|exists:services,id';
                $rules['services.*.customer_count'] = 'required_with:services|integer|min:1';
                $rules['services.*.person_number'] = 'nullable|integer|min:1';
            } elseif ($bookingType === 'hourly') {
                $rules['number_of_hours'] = 'nullable|integer|min:1';
            }

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return $this->error_response('Validation error', $validator->errors());
            }

            DB::beginTransaction();

            try {
                // Update basic fields if provided
                if ($request->filled('date')) {
                    $appointment->date = $request->date;
                }

                if ($request->filled('address_id')) {
                    $appointment->address_id = $request->address_id;
                }

                if ($request->filled('note')) {
                    $appointment->note = $request->note;
                }

                if ($request->filled('payment_type')) {
                    $appointment->payment_type = $request->payment_type;
                }

                // Update services/hours based on booking type
                if ($bookingType === 'service' && $request->filled('services')) {
                    // Delete old services
                    AppointmentService::where('appointment_id', $appointment->id)->delete();

                    // Recalculate pricing with new services
                    $pricingData = $this->calculateAppointmentPricing($providerType, $request);

                    // Update pricing fields
                    $appointment->total_prices = $pricingData['final_total'];
                    $appointment->total_discounts = $pricingData['discount_amount'];
                    $appointment->original_total_price = $pricingData['original_total'];
                    $appointment->discount_id = $pricingData['discount_id'];
                    $appointment->discount_percentage = $pricingData['discount_percentage'];
                    $appointment->discount_amount = $pricingData['discount_amount'];
                    $appointment->has_discount = $pricingData['has_discount'] ? 1 : 2;

                    // Create new appointment services
                    foreach ($request->services as $serviceData) {
                        $serviceInfo = $this->getServicePricingInfoForBooking($providerType->id, $serviceData['service_id']);

                        AppointmentService::create([
                            'appointment_id' => $appointment->id,
                            'service_id' => $serviceData['service_id'],
                            'customer_count' => $serviceData['customer_count'],
                            'person_number' => $serviceData['person_number'] ?? 1,
                            'service_price' => $serviceInfo['current_price'],
                            'total_price' => $serviceInfo['current_price'] * $serviceData['customer_count'],
                            'original_service_price' => $serviceInfo['original_price'],
                            'service_discount_percentage' => $serviceInfo['discount_percentage'] ?? 0,
                            'service_discount_amount' => $serviceInfo['discount_amount_per_service'] ?? 0,
                            'has_service_discount' => $serviceInfo['has_discount'] ? 1 : 2,
                        ]);
                    }
                } elseif ($bookingType === 'hourly' && $request->filled('number_of_hours')) {
                    // Recalculate pricing for hourly booking
                    $pricingData = $this->calculateHourlyPricing($providerType, $request);

                    // Update pricing fields
                    $appointment->total_prices = $pricingData['final_total'];
                    $appointment->total_discounts = $pricingData['discount_amount'];
                    $appointment->original_total_price = $pricingData['original_total'];
                    $appointment->discount_id = $pricingData['discount_id'];
                    $appointment->discount_percentage = $pricingData['discount_percentage'];
                    $appointment->discount_amount = $pricingData['discount_amount'];
                    $appointment->has_discount = $pricingData['has_discount'] ? 1 : 2;
                }

                $appointment->save();

                DB::commit();

                // Load the complete appointment data
                $appointment->load(['appointmentServices.service', 'address', 'providerType.provider', 'discount']);

                return $this->success_response('Appointment updated successfully', [
                    'appointment' => $appointment,
                    'pricing_breakdown' => ($request->filled('services') || $request->filled('number_of_hours')) ? [
                        'original_total' => $appointment->original_total_price,
                        'discount_applied' => $appointment->has_discount == 1,
                        'discount_percentage' => $appointment->discount_percentage,
                        'amount_saved' => $appointment->discount_amount,
                        'final_total' => $appointment->total_prices,
                    ] : null
                ]);
            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }
        } catch (\Exception $e) {
            return $this->error_response('Failed to update appointment', ['error' => $e->getMessage()]);
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

        $appointment = Appointment::find($appointmentId);

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
                DB::beginTransaction();

                // Calculate if fine should be applied
                $fineData = $this->calculateUserCancellationFine($appointment);

                // Update appointment to canceled
                $appointment->appointment_status = 5;
                $appointment->reason_of_cancel = $reason;
                $appointment->canceled_at = now();

                if ($fineData['should_apply_fine']) {
                    $appointment->fine_applied = 1; // Yes
                    $appointment->fine_amount = $fineData['fine_amount'];
                }

                $appointment->save();

                // Apply fine if needed
                if ($fineData['should_apply_fine']) {
                    $fineDiscount = $this->applyUserCancellationFine($appointment, $user, $fineData);

                    $responseData['fine_applied'] = true;
                    $responseData['fine_amount'] = $fineData['fine_amount'];
                    $responseData['fine_details'] = [
                        'id' => $fineDiscount->id,
                        'amount' => $fineData['fine_amount'],
                        'percentage' => $fineData['fine_percentage'],
                        'reason' => $fineData['fine_reason'],
                        'status' => 'Applied',
                        'applied_at' => $fineDiscount->applied_at,
                        'hours_since_creation' => $fineData['hours_since_creation'],
                        'cancellation_threshold_hours' => $fineData['threshold_hours']
                    ];

                    // Refresh user to get updated balance
                    $user->refresh();
                    $responseData['updated_balance'] = $user->balance;
                }

                DB::commit();

                $responseData['appointment'] = $appointment;

                $message = $fineData['should_apply_fine']
                    ? "Appointment canceled. A fine of {$fineData['fine_amount']} has been deducted from your wallet."
                    : 'Appointment canceled successfully';

                return $this->success_response($message, $responseData);
            } catch (\Exception $e) {
                DB::rollBack();
                return $this->error_response('Failed to cancel appointment', ['error' => $e->getMessage()]);
            }
        } else {
            // Handle other status updates
            try {
                $appointment->appointment_status = $request->status;
                if ($request->filled('note')) {
                    $appointment->note = $request->note;
                }
                $appointment->save();

                $responseData['appointment'] = $appointment;

                return $this->success_response('Appointment status updated successfully', $responseData);
            } catch (\Exception $e) {
                return $this->error_response('Failed to update appointment status', ['error' => $e->getMessage()]);
            }
        }
    }

    // ===================== PRIVATE HELPER METHODS =====================

    /**
     * Calculate appointment pricing with discount
     */
    private function calculateAppointmentPricing($providerType, $request)
    {
        $originalTotal = 0;
        $finalTotal = 0;
        $discountAmount = 0;
        $discountId = null;
        $discountPercentage = 0;
        $discountName = null;
        $hasDiscount = false;

        // Service pricing calculation
        foreach ($request->services as $serviceData) {
            $servicePricing = $this->getCurrentServicePrice($providerType->id, $serviceData['service_id']);
            $customerCount = $serviceData['customer_count'];

            $originalServiceTotal = $servicePricing['original_price'] * $customerCount;
            $currentServiceTotal = $servicePricing['current_price'] * $customerCount;

            $originalTotal += $originalServiceTotal;
            $finalTotal += $currentServiceTotal;

            // If any service has discount, mark appointment as having discount
            if ($servicePricing['has_discount'] && !$hasDiscount) {
                $hasDiscount = true;
                $discountId = $servicePricing['discount']['id'];
                $discountPercentage = $servicePricing['discount']['percentage'];
                $discountName = $servicePricing['discount']['name'];
            }
        }

        $discountAmount = $originalTotal - $finalTotal;

        return [
            'original_total' => $originalTotal,
            'final_total' => $finalTotal,
            'discount_amount' => $discountAmount,
            'discount_id' => $discountId,
            'discount_percentage' => $discountPercentage,
            'discount_name' => $discountName,
            'has_discount' => $hasDiscount,
        ];
    }

    /**
     * Get service pricing info for booking
     */
    private function getServicePricingInfoForBooking($providerTypeId, $serviceId)
    {
        $servicePricing = $this->getCurrentServicePrice($providerTypeId, $serviceId);

        $discountAmountPerService = $servicePricing['has_discount'] ?
            ($servicePricing['original_price'] - $servicePricing['current_price']) : 0;

        return [
            'original_price' => $servicePricing['original_price'],
            'current_price' => $servicePricing['current_price'],
            'has_discount' => $servicePricing['has_discount'],
            'discount_percentage' => $servicePricing['has_discount'] ? $servicePricing['discount']['percentage'] : 0,
            'discount_amount_per_service' => $discountAmountPerService,
        ];
    }

    /**
     * Get current service price with discount applied
     */
    private function getCurrentServicePrice($providerTypeId, $serviceId)
    {
        $providerService = DB::table('provider_services')
            ->join('services', 'provider_services.service_id', '=', 'services.id')
            ->where('provider_type_id', $providerTypeId)
            ->where('service_id', $serviceId)
            ->where('is_active', 1)
            ->select('provider_services.*', 'services.name_en', 'services.name_ar')
            ->first();

        if (!$providerService) {
            return [
                'original_price' => 0,
                'current_price' => 0,
                'has_discount' => false,
                'discount' => null
            ];
        }

        $originalPrice = $providerService->price;
        $currentPrice = $originalPrice;
        $hasDiscount = false;
        $discount = null;

        $activeDiscount = Discount::where('provider_type_id', $providerTypeId)
            ->where('discount_type', 'service')
            ->where('is_active', 1)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->with('services')
            ->first();

        if ($activeDiscount) {
            $isEligible = false;

            if ($activeDiscount->services->isEmpty()) {
                $isEligible = true;
            } else {
                $isEligible = $activeDiscount->services->contains('id', $serviceId);
            }

            if ($isEligible) {
                $discountAmount = ($originalPrice * $activeDiscount->percentage) / 100;
                $currentPrice = $originalPrice - $discountAmount;
                $hasDiscount = true;
                $discount = [
                    'id' => $activeDiscount->id,
                    'name' => $activeDiscount->name,
                    'percentage' => $activeDiscount->percentage,
                    'amount_saved' => $discountAmount
                ];
            }
        }

        return [
            'service' => $providerService,
            'original_price' => $originalPrice,
            'current_price' => $currentPrice,
            'has_discount' => $hasDiscount,
            'discount' => $discount
        ];
    }

    /**
     * Calculate potential savings for discount application
     */
    private function calculatePotentialSavings($appointment, $discount)
    {
        $oldTotal = $appointment->total_prices;
        $totalSavings = 0;
        $applicableServices = [];
        $canApply = false;

        if ($appointment->providerType->type->booking_type === 'service') {
            if ($discount->discount_type === 'service') {
                foreach ($appointment->appointmentServices as $appointmentService) {
                    if ($this->isServiceEligibleForDiscount($appointmentService->service_id, $discount)) {
                        $serviceDiscount = ($appointmentService->total_price * $discount->percentage) / 100;
                        $totalSavings += $serviceDiscount;

                        $applicableServices[] = [
                            'service_id' => $appointmentService->service_id,
                            'service_name' => app()->getLocale() == 'ar' ?
                                $appointmentService->service->name_ar :
                                $appointmentService->service->name_en,
                            'original_price' => $appointmentService->service_price,
                            'discount_amount' => $serviceDiscount / $appointmentService->customer_count,
                            'new_price' => $appointmentService->service_price - ($serviceDiscount / $appointmentService->customer_count),
                            'customer_count' => $appointmentService->customer_count,
                            'total_discount' => $serviceDiscount
                        ];
                        $canApply = true;
                    }
                }
            }
        }

        return [
            'old_total' => $oldTotal,
            'new_total' => $oldTotal - $totalSavings,
            'total_savings' => $totalSavings,
            'applicable_services' => $applicableServices,
            'can_apply' => $canApply
        ];
    }

    /**
     * Check if a service is eligible for a discount
     */
    private function isServiceEligibleForDiscount($serviceId, $discount)
    {
        // If no specific services are set, discount applies to all services
        if ($discount->services->isEmpty()) {
            return true;
        }

        // Check if this service is specifically included
        return $discount->services->contains('id', $serviceId);
    }

    /**
     * Generate unique appointment number
     */
    private function generateAppointmentNumber()
    {
        $lastAppointment = Appointment::orderBy('id', 'desc')->first();

        if ($lastAppointment && $lastAppointment->number) {
            $lastNumber = intval($lastAppointment->number);
        } else {
            $lastNumber = 0;
        }

        return $lastNumber + 1;
    }

    /**
     * Get services summary with discount info
     */
    private function getServicesSummaryWithDiscount($appointment)
    {
        if (
            isset($appointment->providerType->type->booking_type) &&
            $appointment->providerType->type->booking_type == 'service'
        ) {

            // Get aggregated services with discount info
            $services = $appointment->appointmentServices->map(function ($appointmentService) {
                $hasServiceDiscount = isset($appointmentService->has_service_discount) && $appointmentService->has_service_discount == 1;
                $originalPrice = $appointmentService->original_service_price ?? $appointmentService->service_price;
                $discountPercentage = $appointmentService->service_discount_percentage ?? 0;
                $discountAmount = $appointmentService->service_discount_amount ?? 0;

                return [
                    'name' => app()->getLocale() == 'ar' ?
                        $appointmentService->service->name_ar :
                        $appointmentService->service->name_en,
                    'customer_count' => $appointmentService->customer_count,
                    'original_service_price' => $originalPrice,
                    'service_price' => $appointmentService->service_price,
                    'total_price' => $appointmentService->total_price,
                    'has_discount' => $hasServiceDiscount,
                    'discount_info' => $hasServiceDiscount ? [
                        'discount_percentage' => $discountPercentage,
                        'discount_amount' => $discountAmount,
                        'amount_saved_per_service' => $discountAmount,
                        'total_saved_for_service' => $discountAmount * $appointmentService->customer_count
                    ] : null
                ];
            });

            // Calculate totals
            $totalOriginal = $appointment->original_total_price ??
                ($appointment->total_prices + $appointment->total_discounts);
            $totalFinal = $appointment->total_prices;
            $totalSaved = $appointment->total_discounts ?? ($totalOriginal - $totalFinal);

            // Get individual customer services grouped by person
            $customerServices = $appointment->appointmentServices
                ->groupBy('person_number')
                ->map(function ($services, $personNumber) {
                    $personOriginalTotal = 0;
                    $personFinalTotal = 0;

                    foreach ($services as $service) {
                        $originalPrice = $service->original_service_price ?? $service->service_price;
                        $personOriginalTotal += $originalPrice;
                        $personFinalTotal += $service->service_price;
                    }

                    return [
                        'person_number' => $personNumber ?? 1,
                        'total_services' => $services->count(),
                        'original_amount' => $personOriginalTotal,
                        'final_amount' => $personFinalTotal,
                        'amount_saved' => $personOriginalTotal - $personFinalTotal,
                        'services' => $services->map(function ($service) {
                            $hasServiceDiscount = isset($service->has_service_discount) && $service->has_service_discount == 1;
                            return [
                                'service_id' => $service->service_id,
                                'service_name' => app()->getLocale() == 'ar' ?
                                    $service->service->name_ar :
                                    $service->service->name_en,
                                'original_price' => $service->original_service_price ?? $service->service_price,
                                'final_price' => $service->service_price,
                                'has_discount' => $hasServiceDiscount,
                                'discount_percentage' => $service->service_discount_percentage ?? 0
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
                'pricing_summary' => [
                    'original_total' => $totalOriginal,
                    'final_total' => $totalFinal,
                    'total_saved' => $totalSaved,
                    'has_discount' => $totalSaved > 0,
                    'delivery_fee' => $appointment->delivery_fee,
                    'coupon_discount' => $appointment->coupon_discount
                ],
                'customer_services' => $customerServices
            ];
        }

        return null;
    }

    /**
     * Get services summary (original method)
     */
    private function getServicesSummary($appointment)
    {
        return $this->getServicesSummaryWithDiscount($appointment);
    }

    /**
     * Get total customers for an appointment
     */
    private function getTotalCustomers($appointment)
    {
        return $appointment->appointmentServices->sum('customer_count');
    }

    /**
     * Get appointment status text
     */
    private function getAppointmentStatusText($status)
    {
        $labels = [
            1 => 'Pending',
            2 => 'Accepted',
            3 => 'On The Way',
            4 => 'Delivered',
            5 => 'Canceled',
            6 => 'Start Work',
            7 => 'Arrived'
        ];

        return $labels[$status] ?? 'Unknown';
    }

    /**
     * Get appointment status label (alias for compatibility)
     */
    private function getAppointmentStatusLabel($status)
    {
        return $this->getAppointmentStatusText($status);
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

 

   private function calculateUserCancellationFine($appointment)
    {
        // ✅ Check if appointment is still in "Accepted" status (status = 2)
        // If yes and more than 10 minutes have passed since acceptance, NO FINE
        if ($appointment->appointment_status == 2) {
            // Get the delay threshold from settings (default 10 minutes)
            $delayThresholdMinutes = 10;
            
            // Calculate minutes since appointment was last updated (when provider accepted it)
            $minutesSinceAcceptance = now()->diffInMinutes($appointment->updated_at);
            
            // If provider hasn't changed status for more than threshold minutes, no fine
            if ($minutesSinceAcceptance >= $delayThresholdMinutes) {
                return [
                    'should_apply_fine' => false,
                    'fine_amount' => 0,
                    'fine_percentage' => 0,
                    'fine_reason' => "No fine applied - Provider did not update status from 'Accepted' to 'On The Way' within {$delayThresholdMinutes} minutes",
                    'hours_until_appointment' => 0,
                    'threshold_hours' => 3,
                    'provider_delayed' => true,
                    'minutes_since_acceptance' => $minutesSinceAcceptance
                ];
            }
        }

        // Normal fine calculation based on time before appointment
        $thresholdHours = 3;
        $finePercentage = 15;

        // Calculate time remaining before appointment
        $appointmentTime = \Carbon\Carbon::parse($appointment->date);
        $now = now();
        $hoursUntilAppointment = $now->diffInHours($appointmentTime, false);

        // Apply fine only if canceling less than 3 hours before appointment
        $shouldApplyFine = $hoursUntilAppointment > 0 && $hoursUntilAppointment <= $thresholdHours;

        // Calculate fine amount
        $fineAmount = 0;
        if ($shouldApplyFine) {
            $fineAmount = ($appointment->total_prices * $finePercentage) / 100;
        }

        $fineReason = $shouldApplyFine
            ? "Late cancellation - Canceled with {$hoursUntilAppointment} hours remaining before appointment (threshold: {$thresholdHours} hours). {$finePercentage}% fine applied."
            : "Cancellation more than {$thresholdHours} hours before appointment ({$hoursUntilAppointment} hours remaining). No fine applied.";

        return [
            'should_apply_fine' => $shouldApplyFine,
            'fine_amount' => round($fineAmount, 2),
            'fine_percentage' => $finePercentage,
            'fine_reason' => $fineReason,
            'hours_until_appointment' => round($hoursUntilAppointment, 2),
            'threshold_hours' => $thresholdHours,
            'provider_delayed' => false
        ];
    }

    /**
     * Apply user cancellation fine
     */
    private function applyUserCancellationFine($appointment, $user, $fineData)
    {
        // Check if user has sufficient balance
        $hasSufficientBalance = $user->balance >= $fineData['fine_amount'];

        // Create wallet transaction if user has balance
        $walletTransactionId = null;
        if ($hasSufficientBalance) {
            $walletTransaction = \App\Models\WalletTransaction::create([
                'user_id' => $user->id,
                'amount' => $fineData['fine_amount'],
                'type_of_transaction' => 2, // Withdrawal/Deduction
                'note' => "Cancellation fine for appointment #{$appointment->number} - {$fineData['fine_reason']}",
            ]);

            // Deduct from user balance
            $user->balance -= $fineData['fine_amount'];
            $user->save();

            $walletTransactionId = $walletTransaction->id;
        }

        // Create fine record
        $fineDiscount = \App\Models\FineDiscount::create([
            'user_id' => $user->id,
            'provider_id' => null,
            'appointment_id' => $appointment->id,
            'category' => 1, // Automatic
            'amount' => $fineData['fine_amount'],
            'percentage' => $fineData['fine_percentage'],
            'original_amount' => $appointment->total_prices,
            'status' => $hasSufficientBalance ? 2 : 1, // Applied if sufficient balance, else pending
            'reason' => $fineData['fine_reason'],
            'notes' => $hasSufficientBalance
                ? 'Fine automatically deducted from user wallet'
                : 'Insufficient balance - fine pending',
            'applied_at' => $hasSufficientBalance ? now() : null,
            'wallet_transaction_id' => $walletTransactionId,
        ]);

        // Send notification to user
        try {
            $title = "Cancellation Fine Applied";
            $body = $hasSufficientBalance
                ? "A fine of {$fineData['fine_amount']} has been deducted from your wallet for late cancellation of appointment #{$appointment->number}."
                : "A fine of {$fineData['fine_amount']} is pending for late cancellation of appointment #{$appointment->number}. Please add balance to your wallet.";

            FCMController::sendMessageToUser($title, $body, $user->id);
        } catch (\Exception $e) {
            \Log::error("Failed to send fine notification to user: " . $e->getMessage());
        }

        // Log the fine application
        \Log::info("User cancellation fine applied", [
            'user_id' => $user->id,
            'appointment_id' => $appointment->id,
            'fine_amount' => $fineData['fine_amount'],
            'hours_since_creation' => $fineData['hours_since_creation'],
            'has_sufficient_balance' => $hasSufficientBalance,
            'fine_id' => $fineDiscount->id,
            'wallet_transaction_id' => $walletTransactionId
        ]);

        return $fineDiscount;
    }

    private function sendNewAppointmentNotificationToProvider($appointment, $user, $providerType)
    {
        try {
            $title = "New Appointment Request";
            $body = "You have a new appointment request from {$user->name} for " . date('F j, Y', strtotime($appointment->date)) . ". Appointment #{$appointment->number}. Total: {$appointment->total_prices} JD.";

            // Save notification to database
            \App\Models\Notification::create([
                'title' => $title,
                'body' => $body,
                'type' => 2, // provider type
                'provider_id' => $providerType->provider->id,
            ]);

            // Send FCM notification
            FCMController::sendMessageToProvider(
                $title,
                $body,
                $providerType->provider->id
            );

            \Log::info("New appointment notification sent to provider ID: {$providerType->provider->id} for appointment #{$appointment->id}");
        } catch (\Exception $e) {
            \Log::error("Failed to send new appointment notification to provider: " . $e->getMessage());
        }
    }
}
