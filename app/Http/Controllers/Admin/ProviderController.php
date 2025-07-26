<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\Option;
use App\Models\Provider;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ProviderController extends Controller
{
   
    public function index(Request $request)
    {
        $query = Provider::query();
        
        // Filter by activation status
        if ($request->has('status') && $request->status != '') {
            $query->where('activate', $request->status);
        }
        
        // Filter by balance type (positive/negative)
        if ($request->has('balance_type') && $request->balance_type != '') {
            if ($request->balance_type == 'positive') {
                $query->where('balance', '>', 0);
            } elseif ($request->balance_type == 'negative') {
                $query->where('balance', '<', 0);
            } elseif ($request->balance_type == 'zero') {
                $query->where('balance', '=', 0);
            }
        }
        
        // Filter by minimum balance
        if ($request->has('min_balance') && $request->min_balance != '') {
            $query->where('balance', '>=', $request->min_balance);
        }
        
        // Filter by maximum balance
        if ($request->has('max_balance') && $request->max_balance != '') {
            $query->where('balance', '<=', $request->max_balance);
        }
        
        // Search by name
        if ($request->has('search') && $request->search != '') {
            $query->where('name_of_manager', 'like', '%' . $request->search . '%');
        }
        
        $providers = $query->orderBy('created_at', 'desc')->get();
        
        return view('admin.providers.index', compact('providers'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.providers.create');
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
            'name_of_manager' => 'required|string|max:255',
            'phone' => 'required|string|unique:providers',
            'email' => 'nullable|email|unique:providers',
            'photo_of_manager' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'fcm_token' => 'nullable|string',
            'password' => 'required',
            'balance' => 'nullable|numeric',
            'activate' => 'nullable|in:1,2',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('providers.create')
                ->withErrors($validator)
                ->withInput();
        }

        $userData = $request->except('photo_of_manager');
    

        // Handle photo upload
        if ($request->has('photo_of_manager')) {
                $the_file_path = uploadImage('assets/admin/uploads', $request->photo_of_manager);
                 $userData['photo_of_manager'] = $the_file_path;
             }
                
        $providerData['password'] = Hash::make($request->password);

        Provider::create($userData);

        return redirect()
            ->route('providers.index')
            ->with('success', 'Provider created successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $provider = Provider::findOrFail($id);
        
        return view('admin.providers.show', compact('provider'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $provider = Provider::findOrFail($id);
        
        return view('admin.providers.edit', compact('provider'));
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
        $provider = Provider::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|unique:providers,phone,' . $id,
            'email' => 'nullable|email|unique:providers,email,' . $id,
            'photo_of_manager' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'fcm_token' => 'nullable|string',
            'password' => 'nullable',
            'balance' => 'nullable|numeric',
            'activate' => 'nullable|in:1,2',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('providers.edit', $id)
                ->withErrors($validator)
                ->withInput();
        }

        $providerData = $request->except('photo_of_manager','password');

        // Handle photo_of_manager upload
          if ($request->has('photo_of_manager')) {
                $the_file_path = uploadImage('assets/admin/uploads', $request->photo_of_manager);
                $providerData['photo_of_manager'] = $the_file_path;
             }
          if ($request->has('password')) {
                $providerData['password'] = Hash::make($request->password);
             }

        $provider->update($providerData);

        return redirect()
            ->route('providers.index')
            ->with('success', 'provider updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $provider = Provider::findOrFail($id);
        
        
        $provider->delete();

        return redirect()
            ->route('providers.index')
            ->with('success', 'provider deleted successfully');
    }

}
