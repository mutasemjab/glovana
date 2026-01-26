<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\Option;
use App\Models\Provider;
use App\Models\ProviderBan;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ProviderController extends Controller
{

    public function cancelProviderRequest(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        DB::beginTransaction();

        try {
            $provider = Provider::findOrFail($id);

            // Prepare notification data
            $title = 'Request Cancelled';
            $body = $request->reason;

            // Save notification to database
            \App\Models\Notification::create([
                'title' => $title,
                'body' => $body,
                'type' => 4, // provider type
                'provider_id' => $provider->id,
            ]);

            // Send FCM notification
            \App\Http\Controllers\Admin\FCMController::sendMessageToProvider(
                $title,
                $body,
                $provider->id
            );

            DB::commit();

            return redirect()->back()->with('success', 'Provider request cancelled and notification sent successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }


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

        // NEW: Filter by provider type
        if ($request->has('type_id') && $request->type_id != '') {
            $query->whereHas('providerTypes', function ($q) use ($request) {
                $q->where('type_id', $request->type_id);
            });
        }

        $providers = $query->orderBy('created_at', 'desc')->get();

        // Get all types for the filter dropdown
        $types = \App\Models\Type::all(); // Adjust the namespace according to your Type model

        return view('admin.providers.index', compact('providers', 'types'));
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
            'activate' => 'nullable|in:1,2,3',
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
            'activate' => 'nullable|in:1,2,3',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('providers.edit', $id)
                ->withErrors($validator)
                ->withInput();
        }

        $providerData = $request->except('photo_of_manager', 'password');

        // Handle photo_of_manager upload
        if ($request->has('photo_of_manager')) {
            $the_file_path = uploadImage('assets/admin/uploads', $request->photo_of_manager);
            $providerData['photo_of_manager'] = $the_file_path;
        }

        if ($request->filled('password')) {
            $providerData['password'] = Hash::make($request->password);
        }

        // Check if activation status is being changed to 1 (active)
        $wasInactive = $provider->activate != 1;
        $isBeingActivated = $request->has('activate') && $request->activate == 1;

        $provider->update($providerData);

        // Send notification if account was just activated
        if ($wasInactive && $isBeingActivated) {
            DB::beginTransaction();

            try {
                $title = 'Account Activated';
                $body = 'Your account has been activated successfully. Please logout and login again to use all features.';

                // Save notification to database
                \App\Models\Notification::create([
                    'title' => $title,
                    'body' => $body,
                    'type' => 4, // provider type
                    'provider_id' => $provider->id,
                ]);

                // Send FCM notification
                \App\Http\Controllers\Admin\FCMController::sendMessageToProvider(
                    $title,
                    $body,
                    $provider->id
                );

                DB::commit();
            } catch (\Exception $e) {
                DB::rollback();
                \Log::error('Failed to send activation notification: ' . $e->getMessage());
            }
        }

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

    public function updateProviderWallet(Request $request)
    {
        $request->validate([
            'provider_id' => 'required|exists:providers,id',
            'amount' => 'required|numeric|min:0.01',
            'type_of_transaction' => 'required|in:1,2',
            'note' => 'nullable|string|max:500'
        ]);

        DB::beginTransaction();

        try {
            $provider = Provider::findOrFail($request->provider_id);
            $amount = $request->amount;
            $transactionType = $request->type_of_transaction;

            // Calculate new balance
            if ($transactionType == 1) {
                // Add to wallet
                $newBalance = $provider->balance + $amount;
            } else {
                // Deduct from wallet
                $newBalance = $provider->balance - $amount;
            }

            // Update provider balance
            $provider->balance = $newBalance;
            $provider->save();

            // Create wallet transaction record
            WalletTransaction::create([
                'provider_id' => $provider->id,
                'admin_id' => auth()->user()->id,
                'amount' => $amount,
                'type_of_transaction' => $transactionType,
                'note' => $request->note
            ]);

            DB::commit();

            $message = $transactionType == 1 ?
                "Successfully added " . number_format($amount, 2) . " JD to " . $provider->name_of_manager . "'s wallet." :
                "Successfully deducted " . number_format($amount, 2) . " JD from " . $provider->name_of_manager . "'s wallet.";

            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'An error occurred while updating the wallet: ' . $e->getMessage());
        }
    }

    public function banHistory($id)
    {
        $provider = Provider::with(['bans' => function ($query) {
            $query->orderBy('created_at', 'desc');
        }, 'bans.admin', 'bans.unbannedByAdmin'])->findOrFail($id);

        return view('admin.providers.bans.history', compact('provider'));
    }

    public function banProvider(Request $request, $id)
    {
        $request->validate([
            'ban_reason' => 'required|string',
            'ban_description' => 'nullable|string|max:1000',
            'ban_type' => 'required|in:temporary,permanent',
            'ban_duration' => 'required_if:ban_type,temporary|nullable|integer|min:1',
            'ban_duration_unit' => 'required_if:ban_type,temporary|nullable|in:hours,days,weeks,months',
        ]);

        DB::beginTransaction();

        try {
            $provider = Provider::findOrFail($id);

            // Deactivate any existing active bans
            ProviderBan::where('provider_id', $provider->id)
                ->where('is_active', true)
                ->update(['is_active' => false]);

            $banData = [
                'provider_id' => $provider->id,
                'admin_id' => auth()->id(),
                'ban_reason' => $request->ban_reason,
                'ban_description' => $request->ban_description,
                'banned_at' => now(),
                'is_permanent' => $request->ban_type === 'permanent',
                'is_active' => true,
            ];

            // Calculate ban_until for temporary bans
            if ($request->ban_type === 'temporary') {
                $duration = $request->ban_duration;
                $unit = $request->ban_duration_unit;

                $banData['ban_until'] = match ($unit) {
                    'hours' => now()->addHours($duration),
                    'days' => now()->addDays($duration),
                    'weeks' => now()->addWeeks($duration),
                    'months' => now()->addMonths($duration),
                };
            }

            $ban = ProviderBan::create($banData);

            // Update provider activation status
            $provider->update(['activate' => 2]); // Inactive

            // Send notification to provider
            $title = __('messages.account_banned');
            $body = $ban->is_permanent
                ? __('messages.permanent_ban_notification', ['reason' => $ban->getReasonText(app()->getLocale())])
                : __('messages.temporary_ban_notification', [
                    'reason' => $ban->getReasonText(app()->getLocale()),
                    'until' => $ban->ban_until->format('Y-m-d H:i')
                ]);

            \App\Models\Notification::create([
                'title' => $title,
                'body' => $body,
                'type' => 4,
                'provider_id' => $provider->id,
            ]);

            \App\Http\Controllers\Admin\FCMController::sendMessageToProvider($title, $body, $provider->id);

            // Logout provider by revoking all tokens
            $provider->tokens()->delete();
            DB::commit();

            return redirect()->back()->with('success', __('messages.provider_banned_successfully'));
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', __('messages.error_occurred') . ': ' . $e->getMessage());
        }
    }

    public function unbanProvider(Request $request, $providerId, $banId)
    {
        $request->validate([
            'unban_reason' => 'required|string|max:500'
        ]);

        DB::beginTransaction();

        try {
            $provider = Provider::findOrFail($providerId);
            $ban = ProviderBan::where('provider_id', $providerId)
                ->where('id', $banId)
                ->where('is_active', true)
                ->firstOrFail();

            // Update ban record
            $ban->update([
                'is_active' => false,
                'unbanned_at' => now(),
                'unbanned_by' => auth()->id(),
                'unban_reason' => $request->unban_reason,
            ]);

            // Reactivate provider
            $provider->update(['activate' => 1]); // Active

            // Send notification
            $title = __('messages.account_unbanned');
            $body = __('messages.unban_notification', ['reason' => $request->unban_reason]);

            \App\Models\Notification::create([
                'title' => $title,
                'body' => $body,
                'type' => 4,
                'provider_id' => $provider->id,
            ]);

            \App\Http\Controllers\Admin\FCMController::sendMessageToProvider($title, $body, $provider->id);

            DB::commit();

            return redirect()->back()->with('success', __('messages.provider_unbanned_successfully'));
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', __('messages.error_occurred') . ': ' . $e->getMessage());
        }
    }
}
