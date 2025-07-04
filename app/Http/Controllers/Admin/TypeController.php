<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TypeController extends Controller
{
    public function index()
    {
        $types = DB::table('types')->get();
        return view('admin.types.index', compact('types'));
    }

   public function create()
    {
        return view('admin.types.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'have_delivery' => 'required',
            'minimum_order' => 'required',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif',
            'booking_type' => 'required|in:hourly,service',
        ]);

        $imageName = null;
        if ($request->hasFile('photo')) {
            $imageName = uploadImage('assets/admin/uploads', $request->photo);
        }

        DB::table('types')->insert([
            'name_en' => $request->name_en,
            'name_ar' => $request->name_ar,
            'minimum_order' => $request->minimum_order,
            'have_delivery' => $request->have_delivery,
            'photo' => $imageName,
            'booking_type' => $request->booking_type,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('types.index')->with('success', __('messages.Type_Created'));
    }

    public function edit($id)
    {
        $type = DB::table('types')->where('id', $id)->first();
        
        if (!$type) {
            return redirect()->route('types.index')->with('error', __('messages.Type_Not_Found'));
        }

        return view('admin.types.edit', compact('type'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'have_delivery' => 'required',
            'minimum_order' => 'required',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif',
            'booking_type' => 'required|in:hourly,service',
        ]);

        // Get the current record to access existing photo
        $currentType = DB::table('types')->where('id', $id)->first();
        
        $imageName = $currentType->photo; // Keep existing photo by default

        // Handle new image upload
        if ($request->hasFile('photo')) {
            // Delete old image if it exists
            if ($currentType->photo) {
                $oldImagePath = base_path('assets/admin/uploads/' . $currentType->photo);
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }
            
            // Upload new image
            $imageName = uploadImage('assets/admin/uploads', $request->photo);
        }

        DB::table('types')->where('id', $id)->update([
            'name_en' => $request->name_en,
            'name_ar' => $request->name_ar,
            'minimum_order' => $request->minimum_order,
            'have_delivery' => $request->have_delivery,
            'photo' => $imageName,
            'booking_type' => $request->booking_type,
            'updated_at' => now(),
        ]);

        return redirect()->route('types.index')->with('success', __('messages.Type_Updated'));
    }
}