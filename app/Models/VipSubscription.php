<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class VipSubscription extends Model
{
    use HasFactory,LogsActivity;

     protected $guarded = [];
      protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'amount_paid' => 'double'
    ];


        public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll() // Log all attributes since you're using $guarded = []
            ->logOnlyDirty() // Only log changed attributes
            ->dontSubmitEmptyLogs() // Don't log if nothing changed
            ->dontLogIfAttributesChangedOnly(['updated_at']) // Don't log if only updated_at changed
            ->setDescriptionForEvent(fn(string $eventName) => "VipSubscription {$eventName}")
            ->useLogName('VipSubscription'); // Custom log name for filtering
    }

    // Relationships
    public function providerType()
    {
        return $this->belongsTo(ProviderType::class);
    }

    // Accessors
    public function getStatusTextAttribute()
    {
        return match($this->status) {
            1 => __('messages.active'),
            2 => __('messages.inactive'), 
            3 => __('messages.expired'),
            default => __('messages.unknown')
        };
    }

    public function getPaymentStatusTextAttribute()
    {
        return match($this->payment_status) {
            1 => __('messages.paid'),
            2 => __('messages.unpaid'),
            default => __('messages.unknown')
        };
    }

    public function getIsActiveAttribute()
    {
        return $this->status == 1 && 
               $this->end_date >= Carbon::now()->toDateString() &&
               $this->start_date <= Carbon::now()->toDateString();
    }

    public function getIsExpiredAttribute()
    {
        return $this->end_date < Carbon::now()->toDateString();
    }

    public function getDaysRemainingAttribute()
    {
        if ($this->is_expired) {
            return 0;
        }
        
        return Carbon::now()->diffInDays($this->end_date, false);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 1)
                    ->where('start_date', '<=', Carbon::now()->toDateString())
                    ->where('end_date', '>=', Carbon::now()->toDateString());
    }

    public function scopeExpired($query)
    {
        return $query->where('end_date', '<', Carbon::now()->toDateString());
    }

    public function scopeExpiringSoon($query, $days = 7)
    {
        return $query->where('status', 1)
                    ->where('end_date', '<=', Carbon::now()->addDays($days)->toDateString())
                    ->where('end_date', '>=', Carbon::now()->toDateString());
    }
}
