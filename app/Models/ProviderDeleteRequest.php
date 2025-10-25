<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ProviderDeleteRequest extends Model
{
    use HasFactory, LogsActivity;

     protected $guarded = [];

    protected $casts = [
        'processed_at' => 'datetime',
        'additional_data' => 'json'
    ];

    // Activity logging configuration
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->dontLogIfAttributesChangedOnly(['updated_at'])
            ->setDescriptionForEvent(fn(string $eventName) => "ProviderDeleteRequest {$eventName}")
            ->useLogName('provider_delete_request');
    }

    /**
     * Get the provider that owns the delete request.
     */
    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    /**
     * Get the admin user who processed the request.
     */
    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Scope a query to only include pending requests.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include approved requests.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope a query to only include rejected requests.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Scope a query to only include processed requests.
     */
    public function scopeProcessed($query)
    {
        return $query->whereIn('status', ['approved', 'rejected']);
    }

    /**
     * Get the status text attribute.
     */
    public function getStatusTextAttribute()
    {
        return match($this->status) {
            'pending' => __('messages.pending'),
            'approved' => __('messages.approved'),
            'rejected' => __('messages.rejected'),
            default => __('messages.unknown')
        };
    }

    /**
     * Get the status color attribute for UI.
     */
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            default => 'secondary'
        };
    }

    /**
     * Check if the request is pending.
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    /**
     * Check if the request is approved.
     */
    public function isApproved()
    {
        return $this->status === 'approved';
    }

    /**
     * Check if the request is rejected.
     */
    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    /**
     * Check if the request has been processed.
     */
    public function isProcessed()
    {
        return in_array($this->status, ['approved', 'rejected']);
    }

    /**
     * Get formatted processing time.
     */
    public function getProcessingTimeAttribute()
    {
        if (!$this->processed_at) {
            return null;
        }

        return $this->processed_at->diffInHours($this->created_at);
    }
}