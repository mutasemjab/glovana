<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProviderImage extends Model
{
    use HasFactory;

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
}