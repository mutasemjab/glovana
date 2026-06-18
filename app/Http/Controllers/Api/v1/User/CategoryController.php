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
   
     public function getProductsFromCategory(Request $request, $id)
     {
         $searchTerm = trim((string) $request->input('search', ''));

         $categories = Category::with([
             'products' => function ($query) use ($searchTerm) {
                 if ($searchTerm !== '') {
                     $query->where(function ($productQuery) use ($searchTerm) {
                         $productQuery->where('name_en', 'LIKE', "%{$searchTerm}%")
                             ->orWhere('name_ar', 'LIKE', "%{$searchTerm}%")
                             ->orWhere('description_en', 'LIKE', "%{$searchTerm}%")
                             ->orWhere('description_ar', 'LIKE', "%{$searchTerm}%")
                             ->orWhere('specification_en', 'LIKE', "%{$searchTerm}%")
                             ->orWhere('specification_ar', 'LIKE', "%{$searchTerm}%");
                     });
                 }

                 $query->with(['images', 'ratings'])->orderByDesc('created_at');
             }
         ])->where('id',$id)->get();
         
         return $this->success_response('Category retrieved successfully', $categories);
     }
   
}
