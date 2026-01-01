<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Appointment extends Model
{
    use HasFactory, LogsActivity;

    protected $guarded = [];
    protected $casts = [
        'date' => 'datetime',
        'delivery_fee' => 'double',
        'total_prices' => 'double',
        'total_discounts' => 'double',
        'coupon_discount' => 'double',
    ];

    protected $appends = ['rating_flag'];

    public function getRatingFlagAttribute()
    {
        // ✅ Step 1: Check if user has rated this provider type
        $hasRatedProviderType = ProviderRating::where('provider_type_id', $this->provider_type_id)
            ->where('user_id', $this->user_id)
            ->exists();

        // ✅ Step 2: If rated, ALWAYS return 2 (don't show rating)
        if ($hasRatedProviderType) {
            return 2;
        }

        // ✅ Step 3: If not rated, check cancel_rating for THIS appointment
        // cancel_rating = 2 → rating_flag = 1 (show rating)
        // cancel_rating = 1 → rating_flag = 2 (don't show rating)
        return ($this->cancel_rating == 2) ? 1 : 2;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll() // Log all attributes since you're using $guarded = []
            ->logOnlyDirty() // Only log changed attributes
            ->dontSubmitEmptyLogs() // Don't log if nothing changed
            ->dontLogIfAttributesChangedOnly(['updated_at']) // Don't log if only updated_at changed
            ->setDescriptionForEvent(fn(string $eventName) => "Appointment {$eventName}")
            ->useLogName('appointment'); // Custom log name for filtering
    }

    public function appointmentServices()
    {
        return $this->hasMany(AppointmentService::class);
    }

    // Relationships
    public function discount()
    {
        return $this->belongsTo(Discount::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function address()
    {
        return $this->belongsTo(UserAddress::class, 'address_id');
    }

    public function providerType()
    {
        return $this->belongsTo(ProviderType::class);
    }

    public function fineDiscounts()
    {
        return $this->hasMany(FineDiscount::class);
    }

    public function latestFine()
    {
        return $this->hasOne(FineDiscount::class)->latest();
    }

}
