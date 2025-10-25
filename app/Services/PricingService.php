<?php

namespace App\Services;

use App\Models\Discount;
use App\Models\ProviderType;
use App\Models\Service;
use Carbon\Carbon;

class PricingService
{
    /**
     * Get the discounted hourly price for a provider type
     */
    public function getDiscountedHourlyPrice($providerTypeId)
    {
        $providerType = ProviderType::findOrFail($providerTypeId);
        $originalPrice = $providerType->price_per_hour;
        
        // Get the best active discount for hourly pricing
        $discount = $this->getBestActiveDiscount($providerTypeId, 'hourly');
        
        if (!$discount) {
            return [
                'original_price' => $originalPrice,
                'discounted_price' => $originalPrice,
                'discount_percentage' => 0,
                'discount_amount' => 0,
                'has_discount' => false,
                'discount' => null
            ];
        }
        
        $discountedPrice = $discount->calculateDiscountedPrice($originalPrice);
        $discountAmount = $discount->getDiscountAmount($originalPrice);
        
        return [
            'original_price' => $originalPrice,
            'discounted_price' => $discountedPrice,
            'discount_percentage' => $discount->percentage,
            'discount_amount' => $discountAmount,
            'has_discount' => true,
            'discount' => $discount
        ];
    }
    
    /**
     * Get the discounted service price for a specific service
     */
    public function getDiscountedServicePrice($providerTypeId, $serviceId)
    {
        // Get the original price from provider_services table
        $providerService = \DB::table('provider_services')
            ->where('provider_type_id', $providerTypeId)
            ->where('service_id', $serviceId)
            ->first();
            
        if (!$providerService) {
            return [
                'original_price' => 0,
                'discounted_price' => 0,
                'discount_percentage' => 0,
                'discount_amount' => 0,
                'has_discount' => false,
                'discount' => null
            ];
        }
        
        $originalPrice = $providerService->price;
        
        // Get the best active discount for service pricing that applies to this service
        $discount = $this->getBestActiveDiscountForService($providerTypeId, $serviceId);
        
        if (!$discount) {
            return [
                'original_price' => $originalPrice,
                'discounted_price' => $originalPrice,
                'discount_percentage' => 0,
                'discount_amount' => 0,
                'has_discount' => false,
                'discount' => null
            ];
        }
        
        $discountedPrice = $discount->calculateDiscountedPrice($originalPrice);
        $discountAmount = $discount->getDiscountAmount($originalPrice);
        
        return [
            'original_price' => $originalPrice,
            'discounted_price' => $discountedPrice,
            'discount_percentage' => $discount->percentage,
            'discount_amount' => $discountAmount,
            'has_discount' => true,
            'discount' => $discount
        ];
    }
    
    /**
     * Get all discounted service prices for a provider type
     */
    public function getAllDiscountedServicePrices($providerTypeId)
    {
        $providerServices = \DB::table('provider_services')
            ->join('services', 'provider_services.service_id', '=', 'services.id')
            ->where('provider_type_id', $providerTypeId)
            ->where('is_active', 1)
            ->select('provider_services.*', 'services.name_en', 'services.name_ar')
            ->get();
            
        $result = [];
        
        foreach ($providerServices as $providerService) {
            $priceInfo = $this->getDiscountedServicePrice($providerTypeId, $providerService->service_id);
            $priceInfo['service'] = $providerService;
            $result[] = $priceInfo; // Use array push instead of associative array
        }
        
        return $result;
    }
    
    /**
     * Get the best active discount for a provider type and discount type
     */
    private function getBestActiveDiscount($providerTypeId, $discountType)
    {
        return Discount::forProviderType($providerTypeId)
            ->forDiscountType($discountType)
            ->active()
            ->current()
            ->orderBy('percentage', 'desc') // Get the highest discount
            ->first();
    }
    
    /**
     * Get the best active discount for a specific service
     */
    private function getBestActiveDiscountForService($providerTypeId, $serviceId)
    {
        // First, try to find discounts that specifically include this service
        $specificDiscount = Discount::forProviderType($providerTypeId)
            ->forDiscountType('service')
            ->active()
            ->current()
            ->whereHas('services', function($query) use ($serviceId) {
                $query->where('service_id', $serviceId);
            })
            ->orderBy('percentage', 'desc')
            ->first();
            
        if ($specificDiscount) {
            return $specificDiscount;
        }
        
        // If no specific discount, try to find discounts that apply to all services (no services specified)
        $generalDiscount = Discount::forProviderType($providerTypeId)
            ->forDiscountType('service')
            ->active()
            ->current()
            ->whereDoesntHave('services') // No specific services means applies to all
            ->orderBy('percentage', 'desc')
            ->first();
            
        return $generalDiscount;
    }
    
    /**
     * Check if a provider type has any active discounts
     */
    public function hasActiveDiscounts($providerTypeId)
    {
        return Discount::forProviderType($providerTypeId)
            ->active()
            ->current()
            ->exists();
    }
    
    /**
     * Get all active discounts for a provider type
     */
    public function getActiveDiscounts($providerTypeId)
    {
        return Discount::forProviderType($providerTypeId)
            ->active()
            ->current()
            ->with('services')
            ->get();
    }
    
    /**
     * Calculate total booking price with discounts
     * For complex bookings that might include both hourly and service charges
     */
    public function calculateBookingPrice($providerTypeId, $hours = 0, $serviceIds = [])
    {
        $totalOriginalPrice = 0;
        $totalDiscountedPrice = 0;
        $totalDiscountAmount = 0;
        $appliedDiscounts = [];
        
        // Calculate hourly pricing if hours specified
        if ($hours > 0) {
            $hourlyPricing = $this->getDiscountedHourlyPrice($providerTypeId);
            $hourlyOriginal = $hourlyPricing['original_price'] * $hours;
            $hourlyDiscounted = $hourlyPricing['discounted_price'] * $hours;
            
            $totalOriginalPrice += $hourlyOriginal;
            $totalDiscountedPrice += $hourlyDiscounted;
            $totalDiscountAmount += ($hourlyOriginal - $hourlyDiscounted);
            
            if ($hourlyPricing['has_discount']) {
                $appliedDiscounts[] = $hourlyPricing['discount'];
            }
        }
        
        // Calculate service pricing if services specified
        if (!empty($serviceIds)) {
            foreach ($serviceIds as $serviceId) {
                $servicePricing = $this->getDiscountedServicePrice($providerTypeId, $serviceId);
                
                $totalOriginalPrice += $servicePricing['original_price'];
                $totalDiscountedPrice += $servicePricing['discounted_price'];
                $totalDiscountAmount += $servicePricing['discount_amount'];
                
                if ($servicePricing['has_discount']) {
                    $appliedDiscounts[] = $servicePricing['discount'];
                }
            }
        }
        
        return [
            'total_original_price' => $totalOriginalPrice,
            'total_discounted_price' => $totalDiscountedPrice,
            'total_discount_amount' => $totalDiscountAmount,
            'total_savings_percentage' => $totalOriginalPrice > 0 ? round(($totalDiscountAmount / $totalOriginalPrice) * 100, 2) : 0,
            'applied_discounts' => array_unique($appliedDiscounts, SORT_REGULAR),
            'has_discounts' => !empty($appliedDiscounts)
        ];
    }
}