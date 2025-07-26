<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions; // Add this import

class Banner extends Model
{
    use HasFactory, LogsActivity;

    protected $guarded = [];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll() // Log all attributes since you're using $guarded = []
            ->logOnlyDirty() // Only log changed attributes
            ->dontSubmitEmptyLogs() // Don't log if nothing changed
            ->dontLogIfAttributesChangedOnly(['updated_at']) // Don't log if only updated_at changed
            ->setDescriptionForEvent(fn(string $eventName) => "Banner {$eventName}") // Fixed description
            ->useLogName('banner'); // Fixed log name to match the model
    }
    
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function providerType()
    {
        return $this->belongsTo(ProviderType::class);
    }
}