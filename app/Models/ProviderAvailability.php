<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
class ProviderAvailability extends Model
{
    use HasFactory,LogsActivity;

    protected $guarded = [];

      public function providerType()
    {
        return $this->belongsTo(ProviderType::class);
    }

    public function getFormattedTimeAttribute()
    {
        return $this->start_time . ' - ' . $this->end_time;
    }

     public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll() // Log all attributes since you're using $guarded = []
            ->logOnlyDirty() // Only log changed attributes
            ->dontSubmitEmptyLogs() // Don't log if nothing changed
            ->dontLogIfAttributesChangedOnly(['updated_at']) // Don't log if only updated_at changed
            ->setDescriptionForEvent(fn(string $eventName) => "ProviderAvailability {$eventName}")
            ->useLogName('ProviderAvailability'); // Custom log name for filtering
    }
}