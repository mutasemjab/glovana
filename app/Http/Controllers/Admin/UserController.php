<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;


class UserController extends Controller
{
    
    public function index()
    {
        // Get users with pagination (15 users per page)
        $users = User::orderBy('created_at', 'desc')->paginate(15);
        
        return view('admin.users.index', compact('users'));
    }

 
    public function create()
    {
        return view('admin.users.create');
    }

   
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:users',
             'password' => 'required',
            'email' => 'nullable|email|unique:users',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'fcm_token' => 'nullable|string',
            'balance' => 'nullable|numeric',
            'activate' => 'nullable|in:1,2',
            'user_id' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('users.create')
                ->withErrors($validator)
                ->withInput();
        }

        $userData = $request->except('photo');
        
        // Generate a referral code if not provided
        if (!isset($userData['referral_code'])) {
            $userData['referral_code'] = Str::random(8);
        }

        // Handle photo upload
        if ($request->has('photo')) {
                $the_file_path = uploadImage('assets/admin/uploads', $request->photo);
                 $userData['photo'] = $the_file_path;
        }

        $userData['password'] = Hash::make($request->password);

        User::create($userData);

        return redirect()
            ->route('users.index')
            ->with('success', 'User created successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::findOrFail($id);
        
        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);
        
        return view('admin.users.edit', compact('user'));
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
        $user = User::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'password' => 'nullable',
            'phone' => 'sometimes|string|unique:users,phone,' . $id,
            'email' => 'nullable|email|unique:users,email,' . $id,
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'fcm_token' => 'nullable|string',
            'balance' => 'nullable|numeric',
            'activate' => 'nullable|in:1,2',
            'user_id' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('users.edit', $id)
                ->withErrors($validator)
                ->withInput();
        }

        $userData = $request->except('photo');

        // Handle photo upload
          if ($request->has('photo')) {
                $the_file_path = uploadImage('assets/admin/uploads', $request->photo);
                $userData['photo'] = $the_file_path;
        }
        
        if ($request->has('password')) {
              $userData['password'] = Hash::make($request->password);
        }
    
        $user->update($userData);

        return redirect()
            ->route('users.index')
            ->with('success', 'User updated successfully');
    }

   
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        
        $user->delete();

        return redirect()
            ->route('users.index')
            ->with('success', 'User deleted successfully');
    }

    public function updateWallet(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0.01',
            'type_of_transaction' => 'required|in:1,2',
            'note' => 'nullable|string|max:500'
        ]);

        DB::beginTransaction();
        
        try {
            $user = User::findOrFail($request->user_id);
            $amount = $request->amount;
            $transactionType = $request->type_of_transaction;
            
            // Calculate new balance
            if ($transactionType == 1) {
                // Add to wallet
                $newBalance = $user->balance + $amount;
            } else {
                // Deduct from wallet
                $newBalance = $user->balance - $amount;
            }
            
            // Update user balance
            $user->balance = $newBalance;
            $user->save();
            
            // Create wallet transaction record
            WalletTransaction::create([
                'user_id' => $user->id,
                'admin_id' => auth()->user()->id, // Assuming admin is logged in
                'amount' => $amount,
                'type_of_transaction' => $transactionType,
                'note' => $request->note
            ]);
            
            DB::commit();
            
            $message = $transactionType == 1 ? 
                "Successfully added JD" . number_format($amount, 2) . " to " . $user->name . "'s wallet." :
                "Successfully deducted JD" . number_format($amount, 2) . " from " . $user->name . "'s wallet.";
                
            return redirect()->back()->with('success', $message);
            
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'An error occurred while updating the wallet: ' . $e->getMessage());
        }
    }
}

