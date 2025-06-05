<?php


namespace App\Http\Controllers\Api\v1\User;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Setting;
use App\Traits\Responses;
use Illuminate\Http\Request;

class ServicesController extends Controller
{ 
    use Responses;
    
     public function index()
    {
        $data = Service::where('activate',1)->with('servicePayments')->get();

          return $this->success_response('Service retrieved successfully', $data);
    }


}
