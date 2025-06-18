<?php


namespace App\Http\Controllers\Api\v1\Provider;

use App\Http\Controllers\Controller;
use App\Models\ProviderRating;
use App\Traits\Responses;
use Illuminate\Http\Request;

class RatingProviderController extends Controller
{
     use Responses;
    
     public function index(Request $request)
    {
        $data = ProviderRating::where('provider_type_id',$request->provider_type_id)->get();

       return $this->success_response('Rating get successfully', $data);
    }


}
