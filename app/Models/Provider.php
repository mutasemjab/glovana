<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;


class Provider extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $guarded = [];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Add all photo URL attributes to the appends array
    protected $appends = [
        'photo_url',
    ];
    
    public function providerTypes()
    {
        return $this->hasMany(ProviderType::class);
    }
    
    /**
     * Helper method to generate image URLs
     *
     * @param string|null $imageName
     * @return string|null
     */
    protected function getImageUrl($imageName)
    {
        if ($imageName) {
            $baseUrl = rtrim(config('app.url'), '/');
            return $baseUrl . '/assets/admin/uploads/' . $imageName;
        }
        
        return null;
    }
    
    // Accessor for photo URL
    public function getPhotoUrlAttribute()
    {
        return $this->getImageUrl($this->photo_of_manager);
    }
    
    public function pointsTransactions()
    {
        return $this->hasMany(PointTransaction::class);
    }

}
