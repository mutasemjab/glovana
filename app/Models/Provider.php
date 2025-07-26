<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Provider extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable,LogsActivity;

    protected $guarded = [];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Add all photo URL attributes to the appends array
    protected $appends = [
        'photo_url',
    ];
    
      public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll() // Log all attributes since you're using $guarded = []
            ->logOnlyDirty() // Only log changed attributes
            ->dontSubmitEmptyLogs() // Don't log if nothing changed
            ->dontLogIfAttributesChangedOnly(['updated_at']) // Don't log if only updated_at changed
            ->setDescriptionForEvent(fn(string $eventName) => "Provider {$eventName}")
            ->useLogName('provider'); // Custom log name for filtering
    }

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

        public function walletTransactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function appointments()
    {
        return $this->hasManyThrough(Appointment::class, ProviderType::class);
    }

}
