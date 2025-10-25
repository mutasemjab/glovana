<?php


namespace App\Http\Controllers\Api\v1\User;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Traits\Responses;
use Illuminate\Http\Request;


class ProductController extends Controller
{
     use Responses;

   
       public function searchProduct(Request $request)
    {
        try {
            $query = Product::query();

            // Debug: Log the search term
            \Log::info('Search term: ' . $request->search);

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

            // Debug: Log the SQL query
            \Log::info('SQL Query: ' . $query->toSql());
            \Log::info('Query Bindings: ', $query->getBindings());

            // Sort by price
            if ($request->has('sort_by') && $request->sort_by === 'price') {
                $sortOrder = $request->get('sort_order', 'asc'); // asc = low to high, desc = high to low
                
                if ($sortOrder === 'desc') {
                    $query->orderBy('price', 'desc'); // High to low
                } else {
                    $query->orderBy('price', 'asc');  // Low to high
                }
            }

            $products = $query->with('images','ratings')->get();

            // Debug: Log results count
            \Log::info('Products found: ' . $products->count());

            // If no search term, return all products
            if (!$request->has('search') || empty($request->search)) {
                $allProducts = Product::with('images','ratings')->get();
                return $this->success_response('All products retrieved successfully', $allProducts);
            }

            return $this->success_response('Products retrieved successfully', $products);

        } catch (\Exception $e) {
            \Log::error('Search error: ' . $e->getMessage());
            return $this->error_response('Error retrieving products', ['error' => $e->getMessage()]);
        }
    }

    public function productDetails($id)
    {
        // Get the product with relations
        $product = Product::with(['images', 'ratings'])->findOrFail($id);

        // Get similar products (same category, excluding current product)
        $similarProducts = Product::with('images')
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->limit(10)
            ->get();

        // Return both in one response
        return $this->success_response('Product retrieved successfully', [
            'product' => $product,
            'similar_products' => $similarProducts
        ]);
    }

   
}
