<?php


namespace App\Http\Controllers\Api\v1\User;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Service;
use App\Models\Setting;
use App\Models\UserAddress;
use App\Traits\Responses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
     use Responses;

     public function index()
     {
         
         $categories = Category::get();
         
         return $this->success_response('Category retrieved successfully', $categories);
     }
   
     public function getProductsFromCategory($id)
     {
         
         $categories = Category::with('products')->where('id',$id)->get();
         
         return $this->success_response('Category retrieved successfully', $categories);
     }
   
}
