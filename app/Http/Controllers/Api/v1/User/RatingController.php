<?php


namespace App\Http\Controllers\Api\v1\User;

use App\Http\Controllers\Controller;
use App\Models\ProductRating;
use App\Models\ProviderRating;
use App\Models\Rating;
use App\Models\Setting;
use App\Traits\Responses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RatingController extends Controller
{
    use Responses;

    public function store(Request $request)
    {
        // Add validation to ensure proper data is submitted
        $validator = Validator::make($request->all(), [
            'rating' => 'required|numeric|min:1|max:5',
            'review' => 'nullable|string|max:500',
            'provider_type_id' => 'required|exists:provider_types,id'
        ]);

        if ($validator->fails()) {
            return $this->error_response('Validation error', $validator->errors());
        }

        $rating = new ProviderRating();
        
        // Associate the rating with the authenticated user
        $rating->user_id = auth()->user()->id;
        $rating->rating = $request->rating;
        $rating->review = $request->review;
        $rating->provider_type_id = $request->provider_type_id;
    
        if ($rating->save()) {
            return $this->success_response('Rating submitted successfully', $rating);
        } else {
            return $this->error_response('Something went wrong', null);
        }
    }
    public function storeRatingProduct(Request $request)
    {
        // Add validation to ensure proper data is submitted
        $validator = Validator::make($request->all(), [
            'rating' => 'required|numeric|min:1|max:5',
            'review' => 'nullable|string|max:500',
            'product_id' => 'required|exists:products,id'
        ]);

        if ($validator->fails()) {
            return $this->error_response('Validation error', $validator->errors());
        }

        $rating = new ProductRating();
        
        // Associate the rating with the authenticated user
        $rating->user_id = auth()->user()->id;
        $rating->rating = $request->rating;
        $rating->review = $request->review;
        $rating->product_id = $request->product_id;
    
        if ($rating->save()) {
            return $this->success_response('Rating submitted successfully', $rating);
        } else {
            return $this->error_response('Something went wrong', null);
        }
    }
}
