<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{

    public function index(Request $request)
    {
        // First, get the total quantities for each product
        $quantitySubquery = DB::table('voucher_products')
            ->select('product_id', DB::raw('COALESCE(SUM(quantity), 0) as total_quantity'))
            ->groupBy('product_id');

        // Main query
        $query = DB::table('products')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->leftJoin('product_images', function($join) {
                $join->on('products.id', '=', 'product_images.product_id')
                    ->whereRaw('product_images.id = (SELECT MIN(id) FROM product_images WHERE product_id = products.id)');
            })
            ->leftJoinSub($quantitySubquery, 'quantities', function($join) {
                $join->on('products.id', '=', 'quantities.product_id');
            })
            ->select(
                'products.*',
                'categories.name_en as category_name_en',
                'categories.name_ar as category_name_ar',
                'product_images.photo as first_image',
                DB::raw('COALESCE(quantities.total_quantity, 0) as total_quantity')
            );

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('products.name_en', 'LIKE', "%{$search}%")
                ->orWhere('products.name_ar', 'LIKE', "%{$search}%")
                ->orWhere('products.description_en', 'LIKE', "%{$search}%")
                ->orWhere('products.description_ar', 'LIKE', "%{$search}%")
                ->orWhere('categories.name_en', 'LIKE', "%{$search}%")
                ->orWhere('categories.name_ar', 'LIKE', "%{$search}%");
            });
        }

        // Category filter
        if ($request->filled('category_id')) {
            $query->where('products.category_id', $request->category_id);
        }

        // Price range filter
        if ($request->filled('min_price')) {
            $query->where('products.price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('products.price', '<=', $request->max_price);
        }


        // Discount filter
        if ($request->filled('has_discount')) {
            if ($request->has_discount == 'yes') {
                $query->whereNotNull('products.price_after_discount')
                    ->whereColumn('products.price_after_discount', '<', 'products.price');
            } elseif ($request->has_discount == 'no') {
                $query->where(function($q) {
                    $q->whereNull('products.price_after_discount')
                    ->orWhereColumn('products.price_after_discount', '=', 'products.price');
                });
            }
        }

        // Quantity filter based on voucher_products
        if ($request->filled('quantity_status')) {
            $minimumQuantity = Setting::getValue('minimum_to_notify_me_for_quantity_products', 2);
            
            if ($request->quantity_status == 'out_of_stock') {
                $query->where(function($q) {
                    $q->whereNull('quantities.total_quantity')
                    ->orWhere('quantities.total_quantity', '=', 0);
                });
            } elseif ($request->quantity_status == 'low_stock') {
                $query->where('quantities.total_quantity', '>', 0)
                    ->where('quantities.total_quantity', '<=', $minimumQuantity);
            } elseif ($request->quantity_status == 'in_stock') {
                $query->where('quantities.total_quantity', '>', $minimumQuantity);
            }
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        $allowedSorts = ['name_en', 'name_ar', 'price', 'created_at', 'total_quantity'];
        if (in_array($sortBy, $allowedSorts)) {
            if ($sortBy == 'name_en' || $sortBy == 'name_ar') {
                $query->orderBy("products.{$sortBy}", $sortOrder);
            } elseif ($sortBy == 'total_quantity') {
                $query->orderBy('quantities.total_quantity', $sortOrder);
            } else {
                $query->orderBy("products.{$sortBy}", $sortOrder);
            }
        }

        $products = $query->paginate(15)->withQueryString();

        // Get categories for filter dropdown
        $categories = DB::table('categories')
            ->select('id', 'name_en', 'name_ar')
            ->orderBy('name_en')
            ->get();

        // Get minimum quantity setting for view
        $minimumQuantity = Setting::getValue('minimum_to_notify_me_for_quantity_products', 2);

        return view('admin.products.index', compact('products', 'categories', 'minimumQuantity'));
    }

    public function create()
    {
        $categories = DB::table('categories')->get();
        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'description_en' => 'required|string',
            'description_ar' => 'required|string',
            'specification_en' => 'nullable|string',
            'specification_ar' => 'nullable|string',
            'sold' => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0',
            'tax' => 'required|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'category_id' => 'nullable|exists:categories,id',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif',
        ]);

        // Calculate price after discount
        $priceAfterDiscount = $request->price;
        if ($request->discount_percentage) {
            $priceAfterDiscount = $request->price - ($request->price * $request->discount_percentage / 100);
        }

        // Insert product
        $productId = DB::table('products')->insertGetId([
            'name_en' => $request->name_en,
            'name_ar' => $request->name_ar,
            'description_en' => $request->description_en,
            'description_ar' => $request->description_ar,
            'specification_en' => $request->specification_en,
            'specification_ar' => $request->specification_ar,
            'sold' => $request->sold,
            'price' => $request->price,
            'tax' => $request->tax,
            'discount_percentage' => $request->discount_percentage,
            'price_after_discount' => $priceAfterDiscount,
            'category_id' => $request->category_id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Handle image uploads
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imageName = uploadImage('assets/admin/uploads/', $image);
                DB::table('product_images')->insert([
                    'product_id' => $productId,
                    'photo' => $imageName,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return redirect()->route('products.index')->with('success', __('messages.Product_Created'));
    }

    public function edit($id)
    {
        $product = DB::table('products')->where('id', $id)->first();
        
        if (!$product) {
            return redirect()->route('products.index')->with('error', __('messages.Product_Not_Found'));
        }

        $categories = DB::table('categories')->get();
        $productImages = DB::table('product_images')->where('product_id', $id)->get();

        return view('admin.products.edit', compact('product', 'categories', 'productImages'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'description_en' => 'required|string',
            'description_ar' => 'required|string',
            'specification_en' => 'nullable|string',
            'specification_ar' => 'nullable|string',
            'sold' => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0',
            'tax' => 'required|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'category_id' => 'nullable|exists:categories,id',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif',
        ]);

        // Calculate price after discount
        $priceAfterDiscount = $request->price;
        if ($request->discount_percentage) {
            $priceAfterDiscount = $request->price - ($request->price * $request->discount_percentage / 100);
        }

        // Update product
        DB::table('products')->where('id', $id)->update([
            'name_en' => $request->name_en,
            'name_ar' => $request->name_ar,
            'description_en' => $request->description_en,
            'description_ar' => $request->description_ar,
            'specification_en' => $request->specification_en,
            'specification_ar' => $request->specification_ar,
            'sold' => $request->sold,
            'price' => $request->price,
            'tax' => $request->tax,
            'discount_percentage' => $request->discount_percentage,
            'price_after_discount' => $priceAfterDiscount,
            'category_id' => $request->category_id,
            'updated_at' => now(),
        ]);

        // Handle new image uploads
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
               $imageName = uploadImage('assets/admin/uploads/', $image);
                DB::table('product_images')->insert([
                    'product_id' => $id,
                    'photo' => $imageName,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return redirect()->route('products.index')->with('success', __('messages.Product_Updated'));
    }

    public function deleteImage($imageId)
    {
        try {
            $image = DB::table('product_images')->where('id', $imageId)->first();
            
            if ($image) {
                // Optional: Delete the physical file from storage
                $imagePath = base_path('assets/admin/uploads/' . $image->photo);
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
                
                // Delete record from database
                DB::table('product_images')->where('id', $imageId)->delete();
                
                return response()->json(['success' => true, 'message' => 'Image deleted successfully']);
            }
            
            return response()->json(['success' => false, 'message' => 'Image not found']);
            
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error deleting image']);
        }
    }
}