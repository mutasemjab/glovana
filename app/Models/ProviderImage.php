<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ProviderImage extends Model
{
    use HasFactory,LogsActivity;

    protected $guarded = [];

    protected $appends = ['photo_url'];

     public function providerType()
    {
        return $this->belongsTo(ProviderType::class);
    }
 

    public function getPhotoUrlAttribute()
    {
        if ($this->photo) {
            $baseUrl = rtrim(config('app.url'), '/');
            return $baseUrl . '/assets/admin/uploads/' . $this->photo;
        }
        return null;
    }

      public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll() // Log all attributes since you're using $guarded = []
            ->logOnlyDirty() // Only log changed attributes
            ->dontSubmitEmptyLogs() // Don't log if nothing changed
            ->dontLogIfAttributesChangedOnly(['updated_at']) // Don't log if only updated_at changed
            ->setDescriptionForEvent(fn(string $eventName) => "ProviderImage {$eventName}")
            ->useLogName('ProviderImage'); // Custom log name for filtering
    }
}