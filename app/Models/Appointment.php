<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

     protected $guarded = [];
     protected $casts = [
        'date' => 'datetime',
        'delivery_fee' => 'double',
        'total_prices' => 'double',
        'total_discounts' => 'double',
        'coupon_discount' => 'double',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function address()
    {
        return $this->belongsTo(UserAddress::class, 'address_id');
    }

    public function providerType()
    {
        return $this->belongsTo(ProviderType::class);
    }
}
