<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProviderRating extends Model
{
    use HasFactory;

    protected $guarded = [];

      public function providerType()
    {
        return $this->belongsTo(ProviderType::class);
    }

       public function user()
    {
        return $this->belongsTo(User::class);
    }
}
