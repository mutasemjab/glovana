<?php

namespace App\Http\Controllers\Api\v1\Provider;

use App\Http\Controllers\Controller;
use App\Models\Discount;
use App\Models\ProviderType;
use App\Models\Service;
use App\Services\PricingService;
use App\Traits\Responses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DiscountController extends Controller
{
    use Responses; 

    protected $pricingService;

    public function __construct(PricingService $pricingService)
    {
        $this->pricingService = $pricingService;
    }


    /**
     * Get discounts for a specific provider type
     */
    public function getByProviderType($providerTypeId)
    {
        $provider = auth()->user();
        
        if (!$provider instanceof \App\Models\Provider) {
            return $this->error_response('Unauthorized', 'Only providers can access discounts');
        }

        // Verify the provider type belongs to the authenticated provider
        $providerType = ProviderType::where('id', $providerTypeId)
            ->where('provider_id', $provider->id)
            ->with('type')
            ->first();

        if (!$providerType) {
            return $this->error_response('Not found', 'Provider type not found or does not belong to you');
        }

        $discounts = Discount::where('provider_type_id', $providerTypeId)
            ->with('services')
            ->orderBy('created_at', 'desc')
            ->get();

        // Add current status and pricing info
        $discounts->map(function($discount) {
            $discount->current_status = $discount->isCurrentlyActive() ? 'active' : 
                ($discount->start_date > now() ? 'upcoming' : 'expired');
            return $discount;
        });

        // Get current pricing with discounts
        $currentPricing = [];
        if ($providerType->type->booking_type == 'hourly') {
            $currentPricing['hourly'] = $this->pricingService->getDiscountedHourlyPrice($providerTypeId);
        } else {
            $currentPricing['services'] = $this->pricingService->getAllDiscountedServicePrices($providerTypeId);
        }

        return $this->success_response('Provider type discounts retrieved successfully', [
            'provider_type' => $providerType,
            'discounts' => $discounts,
            'current_pricing' => $currentPricing,
            'has_active_discounts' => $this->pricingService->hasActiveDiscounts($providerTypeId)
        ]);
    }

    /**
     * Create a new discount
     */
    public function store(Request $request)
    {
        $provider = auth()->user();
        
        if (!$provider instanceof \App\Models\Provider) {
            return $this->error_response('Unauthorized', 'Only providers can create discounts');
        }

        $validator = Validator::make($request->all(), [
            'provider_type_id' => 'required|exists:provider_types,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'percentage' => 'required|numeric|min:0|max:100',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
            'is_active' => 'boolean',
            'service_ids' => 'nullable|array',
            'service_ids.*' => 'exists:services,id'
        ]);

        if ($validator->fails()) {
            // Get all error messages as a flat array and join them
            $errors = $validator->errors()->all();
            $errorMessage = implode(' ', $errors);
            
            return $this->error_response($errorMessage, []);
        }

        // Verify the provider type belongs to the authenticated provider
        $providerType = ProviderType::where('id', $request->provider_type_id)
            ->where('provider_id', $provider->id)
            ->with('type')
            ->first();

        if (!$providerType) {
            return $this->error_response('Not found', 'Provider type not found or does not belong to you');
        }

        try {
            DB::beginTransaction();

            // Automatically set discount_type based on provider type's booking_type
            $discountType = $providerType->type->booking_type ?? 'hourly';

            // Create discount
            $discount = Discount::create([
                'provider_type_id' => $request->provider_type_id,
                'name' => $request->name,
                'description' => $request->description,
                'percentage' => $request->percentage,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'discount_type' => $discountType,
                'is_active' => $request->input('is_active', 1),
            ]);

            // Attach services if specified (only for service-based discounts)
            if ($discountType === 'service' && $request->has('service_ids') && !empty($request->service_ids)) {
                $discount->services()->sync($request->service_ids);
            }

            DB::commit();

            $discount->load(['providerType.type', 'services']);
            $discount->current_status = $discount->isCurrentlyActive() ? 'active' : 
                ($discount->start_date > now() ? 'upcoming' : 'expired');

            return $this->success_response('Discount created successfully', $discount);

        } catch (\Exception $e) {
            DB::rollback();
            return $this->error_response('Error creating discount', $e->getMessage());
        }
    }

    /**
     * Update an existing discount
     */
    public function update(Request $request, $discountId)
    {
        $provider = auth()->user();
        
        if (!$provider instanceof \App\Models\Provider) {
            return $this->error_response('Unauthorized', 'Only providers can update discounts');
        }

        // Find discount and verify it belongs to the provider
        $discount = Discount::whereHas('providerType', function($q) use ($provider) {
            $q->where('provider_id', $provider->id);
        })->with(['providerType.type', 'services'])->find($discountId);

        if (!$discount) {
            return $this->error_response('Not found', 'Discount not found or does not belong to you');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'percentage' => 'sometimes|numeric|min:0|max:100',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after:start_date',
            'is_active' => 'sometimes|boolean',
            'service_ids' => 'nullable|array',
            'service_ids.*' => 'exists:services,id'
        ]);

        if ($validator->fails()) {
            return $this->error_response('Validation error', $validator->errors());
        }

        try {
            DB::beginTransaction();

            // Update discount fields
            $updateData = $request->only(['name', 'description', 'percentage', 'start_date', 'end_date', 'is_active']);
            
            // Automatically set discount_type based on provider type's booking_type
            $discountType = $discount->providerType->type->booking_type ?? 'hourly';
            $updateData['discount_type'] = $discountType;

            $discount->update($updateData);

            // Update services (only for service-based discounts)
            if ($discountType === 'service') {
                if ($request->has('service_ids')) {
                    if (!empty($request->service_ids)) {
                        $discount->services()->sync($request->service_ids);
                    } else {
                        $discount->services()->detach();
                    }
                }
            } else {
                // For hourly discounts, remove any service associations
                $discount->services()->detach();
            }

            DB::commit();

            $discount->load(['providerType.type', 'services']);
            $discount->current_status = $discount->isCurrentlyActive() ? 'active' : 
                ($discount->start_date > now() ? 'upcoming' : 'expired');

            return $this->success_response('Discount updated successfully', $discount);

        } catch (\Exception $e) {
            DB::rollback();
            return $this->error_response('Error updating discount', $e->getMessage());
        }
    }

    /**
     * Delete a discount
     */
    public function destroy($discountId)
    {
        $provider = auth()->user();
        
        if (!$provider instanceof \App\Models\Provider) {
            return $this->error_response('Unauthorized', 'Only providers can delete discounts');
        }

        // Find discount and verify it belongs to the provider
        $discount = Discount::whereHas('providerType', function($q) use ($provider) {
            $q->where('provider_id', $provider->id);
        })->find($discountId);

        if (!$discount) {
            return $this->error_response('Not found', 'Discount not found or does not belong to you');
        }

        try {
            $discount->delete();
            return $this->success_response('Discount deleted successfully',[]);
        } catch (\Exception $e) {
            return $this->error_response('Error deleting discount', $e->getMessage());
        }
    }

   
    
}