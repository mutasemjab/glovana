<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ProviderType extends Model
{
    use HasFactory,LogsActivity;

    protected $guarded = [];
     protected $casts = [
        'lat' => 'float',
        'lng' => 'float',
        'price_per_hour' => 'float',
        'is_vip' => 'integer',


    ];

    protected $appends = ['is_favourite','avg_rating'];

       public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll() // Log all attributes since you're using $guarded = []
            ->logOnlyDirty() // Only log changed attributes
            ->dontSubmitEmptyLogs() // Don't log if nothing changed
            ->dontLogIfAttributesChangedOnly(['updated_at']) // Don't log if only updated_at changed
            ->setDescriptionForEvent(fn(string $eventName) => "ProviderType {$eventName}")
            ->useLogName('ProviderType'); // Custom log name for filtering
    }

    public function getIsFavouriteAttribute()
    {
        if (!auth()->check()) {
            return false;
        }
        
        return $this->favourites()->where('user_id', auth()->id())->exists();
    }

    public function favourites()
    {
        return $this->hasMany(ProviderFavourite::class, 'provider_type_id');
    }

    public function favouritedBy()
    {
        return $this->belongsToMany(User::class, 'provider_favourites', 'provider_type_id', 'user_id');
    }

    // Relationships
    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function type()
    {
        return $this->belongsTo(Type::class);
    }

    public function services()
    {
        return $this->hasMany(ProviderServiceType::class);
    }

     public function providerServices()
    {
        return $this->hasMany(ProviderService::class);
    }
 
    public function ratings()
    {
        return $this->hasMany(ProviderRating::class);
    }

    public function getAvgRatingAttribute()
    {
        // Lazy load safety: if ratings relation already loaded, use it
        if ($this->relationLoaded('ratings')) {
            return round($this->ratings->avg('rating') ?? 0, 1);
        }

        // Otherwise, fetch average directly from DB for performance
        return round($this->ratings()->avg('rating') ?? 0, 1);
    }

    public function images()
    {
        return $this->hasMany(ProviderImage::class);
    }

    public function galleries()
    {
        return $this->hasMany(ProviderGallery::class);
    }

    public function availabilities()
    {
        return $this->hasMany(ProviderAvailability::class);
    }

    public function unavailabilities()
    {
        return $this->hasMany(ProviderUnavailability::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

     public function discounts()
    {
        return $this->hasMany(Discount::class);
    }

    /**
     * Get active discounts
     */
    public function activeDiscounts()
    {
        return $this->hasMany(Discount::class)->active()->current();
    }

    /**
     * Check if provider type has active discounts
     */
    public function hasActiveDiscounts()
    {
        return $this->activeDiscounts()->exists();
    }

    /**
     * Get current hourly price with discount applied
     */
    public function getCurrentHourlyPrice()
    {
        $pricingService = new \App\Services\PricingService();
        return $pricingService->getDiscountedHourlyPrice($this->id);
    }

    /**
     * Get current service prices with discounts applied
     */
    public function getCurrentServicePrices()
    {
        $pricingService = new \App\Services\PricingService();
        return $pricingService->getAllDiscountedServicePrices($this->id);
    }

        // Accessors
    public function getStatusTextAttribute()
    {
        return $this->status == 1 ? 'On' : 'Off';
    }

    public function getActivateTextAttribute()
    {
        return $this->activate == 1 ? 'Active' : 'Inactive';
    }

    public function getIsVipTextAttribute()
    {
        return $this->is_vip == 1 ? 'VIP' : 'Regular';
    }

    public function vipSubscriptions()
    {
        return $this->hasMany(VipSubscription::class);
    }

    public function activeVipSubscription()
    {
        return $this->hasOne(VipSubscription::class)->active();
    }

    public function getIsCurrentlyVipAttribute()
    {
        return $this->activeVipSubscription()->exists();
    }
}
