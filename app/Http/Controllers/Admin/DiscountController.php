<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Discount;
use App\Models\ProviderType;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DiscountController extends Controller
{
    /**
     * Display a listing of discounts for a provider type
     */
    public function index($providerId, $providerTypeId)
    {
        $providerType = ProviderType::with(['provider', 'discounts.services'])->findOrFail($providerTypeId);
        $discounts = $providerType->discounts()->with('services')->orderBy('created_at', 'desc')->get();
        
        return view('admin.discounts.index', compact('providerType', 'discounts', 'providerId'));
    }

    /**
     * Show the form for creating a new discount
     */
    public function create($providerId, $providerTypeId)
    {
        $providerType = ProviderType::with(['provider', 'type'])->findOrFail($providerTypeId);
        $services = Service::all();
        
        return view('admin.discounts.create', compact('providerType', 'services', 'providerId'));
    }

    /**
     * Store a newly created discount
     */
    public function store(Request $request, $providerId, $providerTypeId)
    {
        $providerType = ProviderType::with('type')->findOrFail($providerTypeId);
        
        $validationRules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'percentage' => 'required|numeric|min:0|max:100',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
            'is_active' => 'required|in:0,1',
            'service_ids' => 'nullable|array',
            'service_ids.*' => 'exists:services,id'
        ];

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Automatically set discount_type based on provider type's booking_type
        $discountType = $providerType->type->booking_type ?? 'hourly';

        // Create discount
        $discount = Discount::create([
            'provider_type_id' => $providerTypeId,
            'name' => $request->name,
            'description' => $request->description,
            'percentage' => $request->percentage,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'discount_type' => $discountType,
            'is_active' => $request->is_active,
        ]);

        // Attach services if specified (only for service-based discounts)
        if ($discountType === 'service' && $request->has('service_ids') && !empty($request->service_ids)) {
            $discount->services()->sync($request->service_ids);
        }

        return redirect()->route('discounts.index', [$providerId, $providerTypeId])
            ->with('success', __('messages.Discount_Created_Successfully'));
    }

    /**
     * Show the form for editing a discount
     */
    public function edit($providerId, $providerTypeId, $discountId)
    {
        $providerType = ProviderType::with(['provider', 'type'])->findOrFail($providerTypeId);
        $discount = Discount::with('services')->findOrFail($discountId);
        $services = Service::all();
        $selectedServiceIds = $discount->services->pluck('id')->toArray();
        
        return view('admin.discounts.edit', compact('providerType', 'discount', 'services', 'selectedServiceIds', 'providerId'));
    }

    /**
     * Update the specified discount
     */
    public function update(Request $request, $providerId, $providerTypeId, $discountId)
    {
        $discount = Discount::findOrFail($discountId);
        $providerType = ProviderType::with('type')->findOrFail($providerTypeId);
        
        $validationRules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'percentage' => 'required|numeric|min:0|max:100',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_active' => 'required|in:0,1',
            'service_ids' => 'nullable|array',
            'service_ids.*' => 'exists:services,id'
        ];

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Automatically set discount_type based on provider type's booking_type
        $discountType = $providerType->type->booking_type ?? 'hourly';

        // Update discount
        $discount->update([
            'name' => $request->name,
            'description' => $request->description,
            'percentage' => $request->percentage,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'discount_type' => $discountType,
            'is_active' => $request->is_active,
        ]);

        // Update services (only for service-based discounts)
        if ($discountType === 'service') {
            if ($request->has('service_ids') && !empty($request->service_ids)) {
                $discount->services()->sync($request->service_ids);
            } else {
                $discount->services()->detach();
            }
        } else {
            // For hourly discounts, remove any service associations
            $discount->services()->detach();
        }

        return redirect()->route('discounts.index', [$providerId, $providerTypeId])
            ->with('success', __('messages.Discount_Updated_Successfully'));
    }

    /**
     * Remove the specified discount
     */
    public function destroy($providerId, $providerTypeId, $discountId)
    {
        $discount = Discount::findOrFail($discountId);
        $discount->delete();

        return redirect()->route('discounts.index', [$providerId, $providerTypeId])
            ->with('success', __('messages.Discount_Deleted_Successfully'));
    }

    /**
     * Toggle discount status
     */
    public function toggleStatus($providerId, $providerTypeId, $discountId)
    {
        $discount = Discount::findOrFail($discountId);
        $discount->update(['is_active' => !$discount->is_active]);

        $status = $discount->is_active ? 'activated' : 'deactivated';
        return redirect()->back()->with('success', __('messages.Discount_Status_Updated', ['status' => $status]));
    }
}