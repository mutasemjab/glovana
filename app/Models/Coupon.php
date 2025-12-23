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

    // Coupon types
    const TYPE_PRODUCT = 1;
    const TYPE_APPOINTMENT = 2;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->dontLogIfAttributesChangedOnly(['updated_at'])
            ->setDescriptionForEvent(fn(string $eventName) => "Coupon {$eventName}")
            ->useLogName('coupon');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_coupons')
                    ->withTimestamps();
    }

    public function getTypeNameAttribute()
    {
        return $this->type == self::TYPE_PRODUCT ? 'Products' : 'Appointments';
    }

    public function getIsExpiredAttribute()
    {
        return $this->expired_at->isPast();
    }

    /**
     * Check if coupon is valid for use
     */
    public function isValid($userId, $totalAmount)
    {
        // Check expiration
        if ($this->expired_at->isPast()) {
            return [
                'valid' => false,
                'message' => 'Coupon has expired'
            ];
        }

        // Check minimum total
        if ($totalAmount < $this->minimum_total) {
            return [
                'valid' => false,
                'message' => "Minimum order total of {$this->minimum_total} required"
            ];
        }

        // Check if already used by this user
        $alreadyUsed = $this->users()->where('user_id', $userId)->exists();
        if ($alreadyUsed) {
            return [
                'valid' => false,
                'message' => 'Coupon has already been used'
            ];
        }

        return [
            'valid' => true,
            'message' => 'Coupon is valid'
        ];
    }

    /**
     * Scope for product coupons
     */
    public function scopeForProducts($query)
    {
        return $query->where('type', self::TYPE_PRODUCT);
    }

    /**
     * Scope for appointment coupons
     */
    public function scopeForAppointments($query)
    {
        return $query->where('type', self::TYPE_APPOINTMENT);
    }

    /**
     * Scope for active coupons
     */
    public function scopeActive($query)
    {
        return $query->whereDate('expired_at', '>=', today());
    }
}