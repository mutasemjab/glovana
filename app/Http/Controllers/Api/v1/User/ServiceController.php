<?php

namespace App\Http\Controllers\Api\v1\User;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Coupon;
use App\Models\Service;
use App\Models\Type;
use App\Traits\Responses;

class ServiceController extends Controller
{
    use Responses;

    public function index()
    {
        $services = Service::get();

        return $this->success_response('Available services', $services);
    }

}