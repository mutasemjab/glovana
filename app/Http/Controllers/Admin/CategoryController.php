<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\Coupon;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = DB::table('categories')
            ->leftJoin('categories as parent', 'categories.category_id', '=', 'parent.id')
            ->select(
                'categories.*',
                'parent.name_en as parent_name_en',
                'parent.name_ar as parent_name_ar'
            )
            ->get();
        
        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        $parentCategories = DB::table('categories')
            ->whereNull('category_id')
            ->get();
        
        return view('admin.categories.create', compact('parentCategories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        DB::table('categories')->insert([
            'name_en' => $request->name_en,
            'name_ar' => $request->name_ar,
            'category_id' => $request->category_id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('categories.index')->with('success', __('messages.Category_Created'));
    }

    public function edit($id)
    {
        $category = DB::table('categories')->where('id', $id)->first();
        
        if (!$category) {
            return redirect()->route('categories.index')->with('error', __('messages.Category_Not_Found'));
        }

        // Get parent categories excluding the current category and its children
        $parentCategories = DB::table('categories')
            ->where('id', '!=', $id)
            ->whereNull('category_id')
            ->orWhere(function($query) use ($id) {
                $query->where('category_id', '!=', $id)
                      ->whereNotNull('category_id');
            })
            ->get();

        return view('admin.categories.edit', compact('category', 'parentCategories'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id|not_in:' . $id,
        ]);

        DB::table('categories')->where('id', $id)->update([
            'name_en' => $request->name_en,
            'name_ar' => $request->name_ar,
            'category_id' => $request->category_id,
            'updated_at' => now(),
        ]);

        return redirect()->route('categories.index')->with('success', __('messages.Category_Updated'));
    }
}