<?php

namespace App\Http\Controllers\Api\v1\User;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\Page;
use App\Traits\Responses;

class DeliveryController extends Controller
{
    use Responses;
    
    public function index()
    {
        $data = Delivery::get();

        return $this->success_response('Delivery retrieved successfully', $data);
    }


}
