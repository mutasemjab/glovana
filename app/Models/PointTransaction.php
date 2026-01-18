<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class PointTransaction extends Model
{
    use HasFactory, LogsActivity;

    protected $guarded = [];

     protected $casts = [
        'expires_at' => 'datetime',
        'points' => 'integer',
        'type_of_transaction' => 'integer',
        'status' => 'integer'
    ];
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll() // Log all attributes since you're using $guarded = []
            ->logOnlyDirty() // Only log changed attributes
            ->dontSubmitEmptyLogs() // Don't log if nothing changed
            ->dontLogIfAttributesChangedOnly(['updated_at']) // Don't log if only updated_at changed
            ->setDescriptionForEvent(fn(string $eventName) => "PointTransaction {$eventName}")
            ->useLogName('point_transaction'); // Custom log name for filtering
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

     public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 2);
    }

    public function scopeUsed($query)
    {
        return $query->where('status', 3);
    }

    public function scopeNotExpired($query)
    {
        return $query->where(function($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        })->where('status', 1);
    }

    // Accessors
    public function getTypeTextAttribute()
    {
        return $this->type_of_transaction == 1 ? 'Added' : 'Redeemed';
    }

    public function getStatusTextAttribute()
    {
        $statuses = [
            1 => 'Active',
            2 => 'Expired',
            3 => 'Used'
        ];
        return $statuses[$this->status] ?? 'Unknown';
    }

    public function getSourceTextAttribute()
    {
        $sources = [
            'first_order' => 'First Order Bonus',
            'order_purchase' => 'Order Purchase',
            'rating' => 'Service Rating',
            'salon_booking' => 'Salon Booking',
            'vip_bonus' => 'VIP Salon Bonus',
            'referral' => 'Referral Bonus',
            'admin_adjustment' => 'Admin Adjustment',
            'redemption' => 'Points Redemption'
        ];
        return $sources[$this->source] ?? 'Other';
    }
}