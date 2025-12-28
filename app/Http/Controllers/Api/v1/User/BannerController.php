<?php

namespace App\Http\Controllers\Api\v1\User;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Banner;
use App\Models\Coupon;
use App\Traits\Responses;

class BannerController extends Controller
{
    use Responses;

    public function index()
    {
        $banners = Banner::with('providerType.provider')->get();

        $flag = 2;

        if (auth('user-api')->check()) {

            $userId = auth('user-api')->id();

            $needRating = Appointment::where('user_id', $userId)
                ->where('cancel_rating', 2)
                ->whereDoesntHave('providerRating', function ($q) use ($userId) {
                    $q->where('user_id', $userId);
                })
                ->exists();

            $flag = $needRating ? 1 : 2;
        }

        return $this->success_response('Available banners', [
            'flag' => $flag,
            'banners' => $banners
        ]);
    }
}