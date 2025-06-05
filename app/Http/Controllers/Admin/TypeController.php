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
        ]);

        DB::table('types')->insert([
            'name_en' => $request->name_en,
            'name_ar' => $request->name_ar,
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
        ]);

        DB::table('types')->where('id', $id)->update([
            'name_en' => $request->name_en,
            'name_ar' => $request->name_ar,
            'updated_at' => now(),
        ]);

        return redirect()->route('types.index')->with('success', __('messages.Type_Updated'));
    }
}