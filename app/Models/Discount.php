<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    use HasFactory;

    protected $guarded=[];
     
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'percentage' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    /**
     * Get the provider type that owns the discount
     */
    public function providerType()
    {
        return $this->belongsTo(ProviderType::class);
    }

    /**
     * Get the services that this discount applies to
     */
    public function services()
    {
        return $this->belongsToMany(Service::class, 'discount_services');
    }

    /**
     * Check if discount is currently active and valid
     */
    public function isCurrentlyActive()
    {
        $now = Carbon::now()->toDateString();
        return $this->is_active && 
               $this->start_date <= $now && 
               $this->end_date >= $now;
    }

    /**
     * Scope: Get active discounts
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    /**
     * Scope: Get current discounts (within date range)
     */
    public function scopeCurrent($query)
    {
        $now = Carbon::now()->toDateString();
        return $query->where('start_date', '<=', $now)
                    ->where('end_date', '>=', $now);
    }

    /**
     * Scope: Get discounts for specific provider type
     */
    public function scopeForProviderType($query, $providerTypeId)
    {
        return $query->where('provider_type_id', $providerTypeId);
    }

    /**
     * Scope: Get discounts for specific discount type
     */
    public function scopeForDiscountType($query, $type)
    {
        return $query->where('discount_type', $type);
    }

    /**
     * Get formatted percentage
     */
    public function getFormattedPercentageAttribute()
    {
        return $this->percentage . '%';
    }

    /**
     * Check if discount applies to specific service
     */
    public function appliesToService($serviceId)
    {
        // If no specific services are set, it applies to all services
        if ($this->services()->count() === 0) {
            return true;
        }
        
        return $this->services()->where('service_id', $serviceId)->exists();
    }

    /**
     * Calculate discounted price
     */
    public function calculateDiscountedPrice($originalPrice)
    {
        $discountAmount = ($originalPrice * $this->percentage) / 100;
        return $originalPrice - $discountAmount;
    }

    /**
     * Get discount amount
     */
    public function getDiscountAmount($originalPrice)
    {
        return ($originalPrice * $this->percentage) / 100;
    }
}
