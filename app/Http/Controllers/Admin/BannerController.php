<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Product;
use App\Models\ProviderType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BannerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data= Banner::paginate(PAGINATION_COUNT);

        return view('admin.banners.index',compact('data'));
    }

    public function create()
    {
        $products = Product::get();
        $providerTypes = ProviderType::get();
        return view('admin.banners.create', compact('products', 'providerTypes'));
    }



   public function store(Request $request)
    {
        $validated = $request->validate([
            'photo' => 'required|image',
            'type' => 'required|in:1,2',
            'product_id' => 'nullable|exists:products,id',
            'provider_type_id' => 'nullable|exists:provider_types,id',
        ]);

        try {
            $banner = new Banner();
            $banner->type = $request->type;

            if ($request->type == 1) {
                $banner->product_id = $request->product_id;
                $banner->provider_type_id = null;
            } else {
                $banner->provider_type_id = $request->provider_type_id;
                $banner->product_id = null;
            }

            if ($request->hasFile('photo')) {
                $banner->photo = uploadImage('assets/admin/uploads', $request->file('photo'));
            }

            $banner->save();

            return redirect()->route('banners.index')->with(['success' => 'Banner created']);
        } catch (\Exception $ex) {
            Log::error($ex);
            return redirect()->back()->with(['error' => 'An error occurred: ' . $ex->getMessage()]);
        }
    }


     public function edit($id)
    {
        $data = Banner::findOrFail($id);
        $products = Product::get();
        $providerTypes = ProviderType::get();
        return view('admin.banners.edit', compact('data', 'products', 'providerTypes'));
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
        try {
            $banner = Banner::findOrFail($id);


            if ($request->has('photo')) {
                $the_file_path = uploadImage('assets/admin/uploads', $request->photo);
                $banner->photo = $the_file_path;
             }

             $banner->type = $request->type;

            if ($request->type == 1) {
                $banner->product_id = $request->product_id;
                $banner->provider_type_id = null;
            } else {
                $banner->provider_type_id = $request->provider_type_id;
                $banner->product_id = null;
            }



            if ($banner->save()) {
                return redirect()->route('banners.index')->with(['success' => 'Banner updated']);
            } else {
                return redirect()->back()->with(['error' => 'Something went wrong']);
            }
        } catch (\Exception $ex) {
            // Log the exception for debugging purposes
            Log::error($ex);
            return redirect()->back()
                ->with(['error' => 'An error occurred: ' . $ex->getMessage()])
                ->withInput();
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
       try {

            $item_row = Banner::select("id")->where('id','=',$id)->first();

            if (!empty($item_row)) {

        $flag = Banner::where('id','=',$id)->delete();

        if ($flag) {
            return redirect()->back()
            ->with(['success' => '   Delete Succefully   ']);
            } else {
            return redirect()->back()
            ->with(['error' => '   Something Wrong']);
            }

            } else {
            return redirect()->back()
            ->with(['error' => '   cant reach fo this data   ']);
            }

       } catch (\Exception $ex) {

            return redirect()->back()
            ->with(['error' => ' Something Wrong   ' . $ex->getMessage()]);
            }
    }
}

