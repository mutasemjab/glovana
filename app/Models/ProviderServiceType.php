<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProviderServiceType extends Model
{
    use HasFactory;

    protected $guarded = [];

    // Relationships
    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function type()
    {
        return $this->belongsTo(Type::class);
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
}