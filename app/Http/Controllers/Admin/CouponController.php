<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\Coupon;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CouponController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $coupons = Coupon::get();
        return view('admin.coupons.index', compact('coupons'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.coupons.create',);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:255|unique:coupons',
            'amount' => 'required|numeric|min:0',
            'minimum_total' => 'required|numeric|min:0',
            'expired_at' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('coupons.create')
                ->withErrors($validator)
                ->withInput();
        }

        // Set service_id to null if not applicable
        $couponData = $request->all();

        Coupon::create($couponData);

        return redirect()
            ->route('coupons.index')
            ->with('success', __('messages.Coupon_Created_Successfully'));
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $coupon = Coupon::findOrFail($id);
        return view('admin.coupons.edit', compact('coupon'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $coupon = Coupon::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:255|unique:coupons,code,' . $id,
            'amount' => 'required|numeric|min:0',
            'minimum_total' => 'required|numeric|min:0',
            'expired_at' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('coupons.edit', $id)
                ->withErrors($validator)
                ->withInput();
        }

        // Set service_id to null if not applicable
        $couponData = $request->all();
     

        $coupon->update($couponData);

        return redirect()
            ->route('coupons.index')
            ->with('success', __('messages.Coupon_Updated_Successfully'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $coupon = Coupon::findOrFail($id);
        $coupon->delete();

        return redirect()
            ->route('coupons.index')
            ->with('success', __('messages.Coupon_Deleted_Successfully'));
    }

  
}