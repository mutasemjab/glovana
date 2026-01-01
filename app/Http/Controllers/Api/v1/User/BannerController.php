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
        // Load banners
        $banners = Banner::with('providerType.provider')->get();

        return $this->success_response('Available banners', [
            'banners' => $banners
        ]);
    }
}
