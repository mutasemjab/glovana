<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeliveryController extends Controller
{
    public function index()
    {
        $deliveries = DB::table('deliveries')->get();
        return view('admin.deliveries.index', compact('deliveries'));
    }

    public function create()
    {
        return view('admin.deliveries.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'place' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
        ]);

        DB::table('deliveries')->insert([
            'place' => $request->place,
            'price' => $request->price,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('deliveries.index')->with('success', __('messages.Delivery_Created'));
    }

    public function edit($id)
    {
        $delivery = DB::table('deliveries')->where('id', $id)->first();
        
        if (!$delivery) {
            return redirect()->route('deliveries.index')->with('error', __('messages.Delivery_Not_Found'));
        }

        return view('admin.deliveries.edit', compact('delivery'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'place' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
        ]);

        DB::table('deliveries')->where('id', $id)->update([
            'place' => $request->place,
            'price' => $request->price,
            'updated_at' => now(),
        ]);

        return redirect()->route('deliveries.index')->with('success', __('messages.Delivery_Updated'));
    }
}