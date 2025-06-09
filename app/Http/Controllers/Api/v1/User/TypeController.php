<?php

namespace App\Http\Controllers\Api\v1\User;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Coupon;
use App\Models\Service;
use App\Models\Type;
use App\Traits\Responses;

class TypeController extends Controller
{
    use Responses;

    public function index()
    {
        $types = Type::get();

        return $this->success_response('Available types', $types);
    }

}