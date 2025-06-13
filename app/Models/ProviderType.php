<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ProviderType extends Model
{
    use HasFactory;
    protected $guarded = [];
     protected $casts = [
        'lat' => 'float',
        'lng' => 'float',
        'price_per_hour' => 'float',
    ];

    protected $appends = ['is_favourite'];

     public function getIsFavouriteAttribute()
    {
        if (!auth()->check()) {
            return 0;
        }
        
        return DB::table('provider_favourites')
            ->where('provider_type_id', $this->id)
            ->where('user_id', auth()->id())
            ->exists() ? 1 : 0;
    }

    public function favouritedBy()
    {
        return $this->belongsToMany(User::class, 'provider_favourites', 'provider_type_id', 'user_id');
    }

    // Relationships
    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function type()
    {
        return $this->belongsTo(Type::class);
    }

    public function services()
    {
        return $this->hasMany(ProviderServiceType::class);
    }
 
    public function ratings()
    {
        return $this->hasMany(ProviderRating::class);
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

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
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
