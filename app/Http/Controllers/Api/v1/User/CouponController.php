<?php

namespace App\Http\Controllers\Api\v1\User;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Service;
use App\Traits\Responses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;


class CouponController extends Controller
{
    use Responses;

    /**
     * List available coupons for the user
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();

        // Get active coupons
        $coupons = Coupon::where('activate', 1)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->with('service:id,name')
            ->orderBy('end_date')
            ->get();

        // Filter out first ride coupons if user has already completed rides
        $hasCompletedRides = Order::where('user_id', $user->id)
            ->where('status', 5) // Completed status
            ->exists();

        if ($hasCompletedRides) {
            $coupons = $coupons->filter(function ($coupon) {
                return $coupon->coupon_type != 2; // Filter out first ride coupons
            })->values();
        }

        // Add helper attributes
        $coupons->transform(function ($coupon) {
            $coupon->discount_type_text = $coupon->getDiscountTypeText();
            $coupon->coupon_type_text = $coupon->getCouponTypeText();
            $coupon->formatted_discount = $coupon->getFormattedDiscount();
            $coupon->days_remaining = now()->diffInDays($coupon->end_date);
            return $coupon;
        });

        return $this->success_response('Available coupons retrieved successfully', $coupons);
    }

    /**
     * Validate a coupon code for a service
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function validateCoupon(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:255',
            'service_id' => 'required|exists:services,id',
            'amount' => 'required|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return $this->error_response('Validation error', $validator->errors());
        }

        // Get the coupon
        $couponCode = strtoupper($request->code);
        $coupon = Coupon::where('code', $couponCode)
            ->where('activate', 1)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->with('service:id,name')
            ->first();

        if (!$coupon) {
            return $this->error_response('Invalid or expired coupon code', null);
        }

        // Check for service-specific coupon
        if ($coupon->coupon_type == 3 && $coupon->service_id != $request->service_id) {
            return $this->error_response('This coupon is only valid for ' . $coupon->service->name . ' service', null);
        }

        // Check for first ride coupon
        if ($coupon->coupon_type == 2) {
            $hasCompletedRides = Order::where('user_id', $user->id)
                ->where('status', 5) // Completed status
                ->exists();

            if ($hasCompletedRides) {
                return $this->error_response('This coupon is only valid for your first ride', null);
            }
        }

        // Check minimum amount
        if ($request->amount < $coupon->minimum_amount) {
            return $this->error_response(
                'The order amount does not meet the minimum requirement of $' . 
                number_format($coupon->minimum_amount, 2) . 
                ' for this coupon', 
                null
            );
        }

        // Calculate discount
        $discountAmount = $coupon->calculateDiscount($request->amount);
        $finalAmount = $request->amount - $discountAmount;

        $responseData = [
            'coupon' => [
                'id' => $coupon->id,
                'code' => $coupon->code,
                'title' => $coupon->title,
                'discount_type' => $coupon->discount_type,
                'discount_type_text' => $coupon->getDiscountTypeText(),
                'discount' => $coupon->discount,
                'formatted_discount' => $coupon->getFormattedDiscount()
            ],
            'original_amount' => (float) $request->amount,
            'discount_amount' => (float) $discountAmount,
            'final_amount' => (float) $finalAmount
        ];

        return $this->success_response('Coupon applied successfully', $responseData);
    }
}
