<?php

namespace App\Http\Controllers\Api\v1\User;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Banner;
use App\Models\Coupon;
use App\Traits\Responses;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    use Responses;

   public function index(Request $request)
    {
        $token = $request->bearerToken();
        $authenticatedUser = null;
        $ratingFlag = 0;
        $appointmentToRate = null;

        // Optional authentication
        if ($token) {
            $authenticatedUser = Auth::guard('user-api')->user();

            if ($authenticatedUser) {

                // Get FIRST appointment that needs rating
                $appointmentToRate = Appointment::with([
                        'providerType',
                        'providerType.provider:id,name_of_manager,phone,photo_of_manager',
                        'providerType.type'
                    ])
                    ->where('user_id', $authenticatedUser->id)
                    ->where('cancel_rating', 2)
                    ->where('appointment_status', 4) // Delivered (recommended)
                    ->where('payment_status', 1)     // Paid (recommended)
                    ->whereDoesntHave('providerRating')
                    ->orderBy('date', 'desc')
                    ->first();

                $ratingFlag = $appointmentToRate ? 1 : 2;
            }
        }

        // Load banners
        $banners = Banner::with('providerType.provider')->get();

        return $this->success_response('Available banners', [
            'flag' => $ratingFlag,
            'appointment_to_rate' => $appointmentToRate,
            'banners' => $banners
        ]);
    }
}
