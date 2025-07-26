<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Coupon extends Model
{
    use HasFactory, LogsActivity;

    protected $guarded = [];

    protected $casts = [
        'expired_at' => 'date',
        'amount' => 'double',
        'minimum_total' => 'double',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll() // Log all attributes since you're using $guarded = []
            ->logOnlyDirty() // Only log changed attributes
            ->dontSubmitEmptyLogs() // Don't log if nothing changed
            ->dontLogIfAttributesChangedOnly(['updated_at']) // Don't log if only updated_at changed
            ->setDescriptionForEvent(fn(string $eventName) => "Coupon {$eventName}")
            ->useLogName('coupon'); // Custom log name for filtering
    }

    // Many-to-many relationship with User through user_coupons pivot table
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_coupons')
                    ->withTimestamps(); // This will include created_at and updated_at from pivot table
    }

    // Accessor for coupon type name
    public function getTypeNameAttribute()
    {
        return $this->type == 1 ? 'Products' : 'Provider';
    }

    // Check if coupon is expired
    public function getIsExpiredAttribute()
    {
        return $this->expired_at->isPast();
    }
}