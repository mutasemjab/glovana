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

   
     public function productDetails($id)
     {
         
         $products = Product::with('images','ratings')->where('id',$id)->get();
         
         return $this->success_response('Product retrieved successfully', $products);
     }
   
}
