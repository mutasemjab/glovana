<?php

namespace App\Services;

use App\Models\Coupon;
use Illuminate\Support\Facades\DB;

class CouponService
{
    /**
     * Validate and apply coupon for products
     */
    public function validateProductCoupon($couponCode, $userId, $totalBeforeTax)
    {
        $coupon = Coupon::where('code', $couponCode)
            ->forProducts()
            ->active()
            ->first();

        if (!$coupon) {
            return [
                'success' => false,
                'message' => 'Invalid or expired coupon code for products'
            ];
        }

        $validation = $coupon->isValid($userId, $totalBeforeTax);
        
        if (!$validation['valid']) {
            return [
                'success' => false,
                'message' => $validation['message']
            ];
        }

        return [
            'success' => true,
            'coupon' => $coupon,
            'discount' => $coupon->amount,
            'message' => 'Coupon applied successfully'
        ];
    }

    /**
     * Validate and apply coupon for appointments
     */
    public function validateAppointmentCoupon($couponCode, $userId, $totalPrice)
    {
        $coupon = Coupon::where('code', $couponCode)
            ->forAppointments()
            ->active()
            ->first();

        if (!$coupon) {
            return [
                'success' => false,
                'message' => 'Invalid or expired coupon code for appointments'
            ];
        }

        $validation = $coupon->isValid($userId, $totalPrice);
        
        if (!$validation['valid']) {
            return [
                'success' => false,
                'message' => $validation['message']
            ];
        }

        return [
            'success' => true,
            'coupon' => $coupon,
            'discount' => $coupon->amount,
            'message' => 'Coupon applied successfully'
        ];
    }

    /**
     * Mark coupon as used
     */
    public function markAsUsed($couponId, $userId)
    {
        DB::table('user_coupons')->insert([
            'user_id' => $userId,
            'coupon_id' => $couponId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}