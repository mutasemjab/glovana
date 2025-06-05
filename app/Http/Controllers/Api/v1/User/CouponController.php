<?php

namespace App\Http\Controllers\Api\v1\User;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Traits\Responses;
use Illuminate\Http\Request;
use App\Models\UserCoupon;
use Carbon\Carbon;

class CouponController extends Controller
{
    use Responses;

    /**
     * Show all available (non-expired) coupons.
     */
    public function index()
    {
        $coupons = Coupon::whereDate('expired_at', '>=', now())->get();

        return $this->success_response('Available coupons', $coupons);
    }

    /**
     * Validate a coupon code for a specific user and cart total.
     */
    public function validateCoupon(Request $request)
    {
        $request->validate([
            'coupon_code' => 'required|string',
            'total_amount' => 'required|numeric|min:0'
        ]);

        $user = $request->user();

        $coupon = Coupon::where('code', $request->coupon_code)
            ->whereDate('expired_at', '>=', now())
            ->first();

        if (!$coupon) {
            return $this->error_response('Coupon not found or expired', []);
        }

        // Check if user already used this coupon
        $alreadyUsed = UserCoupon::where('user_id', $user->id)
            ->where('coupon_id', $coupon->id)
            ->exists();

        if ($alreadyUsed) {
            return $this->error_response('Coupon already used by this user', []);
        }

        if ($request->total_amount < $coupon->minimum_total) {
            return $this->error_response("Minimum total amount for this coupon is {$coupon->minimum_total}", []);
        }

        return $this->success_response('Coupon is valid', $coupon);
    }
}

