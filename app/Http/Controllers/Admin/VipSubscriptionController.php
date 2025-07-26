<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VipSubscription;
use App\Models\ProviderType;
use Illuminate\Http\Request;
use Carbon\Carbon;

class VipSubscriptionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = VipSubscription::with(['providerType.provider', 'providerType.type']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by payment status
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('start_date', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->where('end_date', '<=', $request->date_to);
        }

        // Search by provider name or salon name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('providerType', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('provider', function($pq) use ($search) {
                      $pq->where('name_of_manager', 'like', "%{$search}%");
                  });
            });
        }

        $subscriptions = $query->orderBy('created_at', 'desc')->paginate(15);

        // Statistics
        $stats = [
            'total' => VipSubscription::count(),
            'active' => VipSubscription::active()->count(),
            'expired' => VipSubscription::expired()->count(),
            'expiring_soon' => VipSubscription::expiringSoon()->count(),
            'total_revenue' => VipSubscription::where('payment_status', 1)->sum('amount_paid')
        ];

        return view('admin.vip-subscriptions.index', compact('subscriptions', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $providerTypes = ProviderType::with(['provider', 'type'])->get();
        
        return view('admin.vip-subscriptions.create', compact('providerTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'provider_type_id' => 'required|exists:provider_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'amount_paid' => 'required|numeric|min:0',
            'status' => 'required|in:1,2',
            'payment_status' => 'required|in:1,2',
            'payment_method' => 'nullable|string|max:255',
            'notes' => 'nullable|string'
        ]);

        $subscription = VipSubscription::create($request->all());

        // Update provider_type is_vip status
        if ($subscription->status == 1 && $subscription->is_active) {
            $subscription->providerType->update(['is_vip' => 1]);
        }

        return redirect()->route('admin.vip-subscriptions.index')
                        ->with('success', __('messages.subscription_created_successfully'));
    }

    /**
     * Display the specified resource.
     */
    public function show(VipSubscription $vipSubscription)
    {
        $vipSubscription->load(['providerType.provider', 'providerType.type']);
        
        return view('admin.vip-subscriptions.show', compact('vipSubscription'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(VipSubscription $vipSubscription)
    {
        $providerTypes = ProviderType::with(['provider', 'type'])->get();
        
        return view('admin.vip-subscriptions.edit', compact('vipSubscription', 'providerTypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, VipSubscription $vipSubscription)
    {
        $request->validate([
            'provider_type_id' => 'required|exists:provider_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'amount_paid' => 'required|numeric|min:0',
            'status' => 'required|in:1,2,3',
            'payment_status' => 'required|in:1,2',
            'payment_method' => 'nullable|string|max:255',
            'notes' => 'nullable|string'
        ]);

        $vipSubscription->update($request->all());

        // Update provider_type is_vip status
        if ($vipSubscription->status == 1 && $vipSubscription->is_active) {
            $vipSubscription->providerType->update(['is_vip' => 1]);
        } else {
            $vipSubscription->providerType->update(['is_vip' => 2]);
        }

        return redirect()->route('admin.vip-subscriptions.index')
                        ->with('success', __('messages.subscription_updated_successfully'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(VipSubscription $vipSubscription)
    {
        // Update provider_type is_vip status to not VIP
        $vipSubscription->providerType->update(['is_vip' => 2]);
        
        $vipSubscription->delete();

        return redirect()->route('admin.vip-subscriptions.index')
                        ->with('success', __('messages.subscription_deleted_successfully'));
    }

    /**
     * Update expired subscriptions
     */
    public function updateExpiredSubscriptions()
    {
        $expiredSubscriptions = VipSubscription::where('end_date', '<', Carbon::now()->toDateString())
                                              ->where('status', '!=', 3)
                                              ->get();

        foreach ($expiredSubscriptions as $subscription) {
            $subscription->update(['status' => 3]); // Mark as expired
            $subscription->providerType->update(['is_vip' => 2]); // Remove VIP status
        }

        return response()->json([
            'message' => __('messages.expired_subscriptions_updated'),
            'count' => $expiredSubscriptions->count()
        ]);
    }
}