<?php


namespace App\Http\Controllers\Api\v1\User;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Service;
use App\Models\Setting;
use App\Models\UserAddress;
use App\Traits\Responses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
     use Responses;

   
     public function searchProduct(Request $request)
    {
        try {
            $query = Product::query();

            // Search by product name, description, or specification
            if ($request->has('search') && !empty($request->search)) {
                $searchTerm = $request->search;
                $query->where(function($q) use ($searchTerm) {
                    $q->where('name_en', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('name_ar', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('description_en', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('description_ar', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('specification_en', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('specification_ar', 'LIKE', "%{$searchTerm}%");
                });
            }

            // Sort by price
            if ($request->has('sort_by') && $request->sort_by === 'price') {
                $sortOrder = $request->get('sort_order', 'asc'); // asc = low to high, desc = high to low
                
                if ($sortOrder === 'desc') {
                    $query->orderBy('price', 'desc'); // High to low
                } else {
                    $query->orderBy('price', 'asc');  // Low to high
                }
            }

            $products = $query->get();

            return $this->success_response('Products retrieved successfully', $products);

        } catch (\Exception $e) {
            return $this->error_response('Error retrieving products', null);
        }
    }
    
     public function productDetails($id)
     {
         
         $products = Product::with('images','ratings')->where('id',$id)->get();
         
         return $this->success_response('Product retrieved successfully', $products);
     }
   
}
