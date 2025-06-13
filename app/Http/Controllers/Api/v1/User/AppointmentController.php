<?php

namespace App\Http\Controllers\Api\v1\User;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\ProviderType;
use App\Models\Coupon;
use App\Models\UserCoupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Traits\Responses;

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
                'user:id,name,email,phone',
                'address:id,address,lat,lng,city,state',
                'providerType:id,name,description,address,lat,lng,price_per_hour,status,is_vip',
                'providerType.provider:id,name,email,phone',
                'providerType.type:id,name,description'
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

            // Transform the data to include status labels
            $appointments->getCollection()->transform(function ($appointment) {
                $appointment->appointment_status_label = $this->getAppointmentStatusLabel($appointment->appointment_status);
                $appointment->payment_status_label = $this->getPaymentStatusLabel($appointment->payment_status);
                $appointment->is_vip_label = $appointment->providerType->is_vip == 1 ? 'VIP' : 'Regular';
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

    /**
     * Store a newly created appointment
     */
    public function store(Request $request)
    {
        try {
            $user = Auth::user();

            // Validation rules
            $validator = Validator::make($request->all(), [
                'provider_type_id' => 'required|exists:provider_types,id',
                'address_id' => 'required|exists:user_addresses,id',
                'date' => 'required|date|after:now',
                'payment_type' => 'required|string|in:cash,visa,wallet',
                'delivery_fee' => 'required|numeric|min:0',
                'total_prices' => 'required|numeric|min:0',
                'total_discounts' => 'nullable|numeric|min:0',
                'coupon_code' => 'nullable|string|exists:coupons,code',
                'note' => 'nullable|string|max:500'
            ]);

            if ($validator->fails()) {
                return $this->error_response(
                    'Validation failed',
                    $validator->errors()
                );
            }

            // Check if provider type is active and available
            $providerType = ProviderType::where('id', $request->provider_type_id)
                ->where('activate', 1)
                ->where('status', 1)
                ->first();

            if (!$providerType) {
                return $this->error_response(
                    'Provider type is not available',
                    []
                );
            }

            // Check if user has conflicting appointments at the same time
            $conflictingAppointment = Appointment::where('user_id', $user->id)
                ->where('date', $request->date)
                ->whereIn('appointment_status', [1, 2, 3]) // Pending, Accepted, OnTheWay
                ->exists();

            if ($conflictingAppointment) {
                return $this->error_response(
                    'You already have an appointment at this time',
                    []
                );
            }

            $couponDiscount = 0;
            $couponId = null;

            // Handle coupon if provided
            if ($request->has('coupon_code') && $request->coupon_code) {
                $coupon = Coupon::where('code', $request->coupon_code)
                    ->where('type', 2) // Coupon for provider type
                    ->where('expired_at', '>=', now())
                    ->first();

                if (!$coupon) {
                    return $this->error_response(
                        'Invalid or expired coupon code',
                        []
                    );
                }

                // Check if user already used this coupon
                $userCouponUsed = UserCoupon::where('user_id', $user->id)
                    ->where('coupon_id', $coupon->id)
                    ->exists();

                if ($userCouponUsed) {
                    return $this->error_response(
                        'You have already used this coupon',
                        []
                    );
                }

                // Check minimum total requirement
                if ($request->total_prices < $coupon->minimum_total) {
                    return $this->error_response(
                        "Minimum total of {$coupon->minimum_total} required to use this coupon",
                        []
                    );
                }

                $couponDiscount = $coupon->amount;
                $couponId = $coupon->id;
            }

            // Generate appointment number
            $appointmentNumber = $this->generateAppointmentNumber();

            // Create appointment
            $appointment = Appointment::create([
                'number' => $appointmentNumber,
                'appointment_status' => 1, // Pending
                'delivery_fee' => $request->delivery_fee,
                'total_prices' => $request->total_prices,
                'total_discounts' => $request->total_discounts ?? 0,
                'coupon_discount' => $couponDiscount,
                'payment_type' => $request->payment_type,
                'payment_status' => 2, // Unpaid
                'date' => $request->date,
                'note' => $request->note,
                'user_id' => $user->id,
                'address_id' => $request->address_id,
                'provider_type_id' => $request->provider_type_id,
            ]);

            // If coupon was used, record it
            if ($couponId) {
                UserCoupon::create([
                    'user_id' => $user->id,
                    'coupon_id' => $couponId
                ]);
            }

            // Load relationships for response
            $appointment->load([
                'user:id,name,email,phone',
                'address:id,address,lat,lng,city,state',
                'providerType:id,name,description,address,lat,lng,price_per_hour,status,is_vip',
                'providerType.provider:id,name,email,phone',
                'providerType.type:id,name,description'
            ]);

            // Add status labels
            $appointment->appointment_status_label = $this->getAppointmentStatusLabel($appointment->appointment_status);
            $appointment->payment_status_label = $this->getPaymentStatusLabel($appointment->payment_status);
            $appointment->is_vip_label = $appointment->providerType->is_vip == 1 ? 'VIP' : 'Regular';

            return $this->success_response(
                'Appointment created successfully',
                $appointment
            );

        } catch (\Exception $e) {
            return $this->error_response(
                'Failed to create appointment',
                ['error' => $e->getMessage()]
            );
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
}