<?php

namespace App\Http\Controllers\Api\v1\User;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Coupon;
use App\Traits\Responses;

class BannerController extends Controller
{
    use Responses;

    public function index()
    {
        $banners = Banner::with('providerType.provider')->get();

        return $this->success_response('Available banners', $banners);
    }
}