<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServiceController extends Controller
{
    public function index()
    {
        $services = DB::table('services')->get();
        return view('admin.services.index', compact('services'));
    }

    public function create()
    {
        return view('admin.services.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
        ]);

        DB::table('services')->insert([
            'name_en' => $request->name_en,
            'name_ar' => $request->name_ar,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('services.index')->with('success', __('messages.Service_Created'));
    }

    public function edit($id)
    {
        $service = DB::table('services')->where('id', $id)->first();
        
        if (!$service) {
            return redirect()->route('services.index')->with('error', __('messages.Service_Not_Found'));
        }

        return view('admin.services.edit', compact('service'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
        ]);

        DB::table('services')->where('id', $id)->update([
            'name_en' => $request->name_en,
            'name_ar' => $request->name_ar,
            'updated_at' => now(),
        ]);

        return redirect()->route('services.index')->with('success', __('messages.Service_Updated'));
    }
}