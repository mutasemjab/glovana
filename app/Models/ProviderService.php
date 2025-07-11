<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProviderService extends Model
{
    use HasFactory;
    protected $guarded = [];

     protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'integer'
    ];

    // Relationships
    public function providerType()
    {
        return $this->belongsTo(ProviderType::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    // Accessors
    public function getFormattedPriceAttribute()
    {
        return number_format($this->price, 2);
    }

    public function getIsActiveTextAttribute()
    {
        return $this->is_active == 1 ? 'Active' : 'Inactive';
    }
}
