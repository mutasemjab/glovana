<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProviderServiceType extends Model
{
    use HasFactory;

    protected $guarded = [];

  public function providerType()
    {
        return $this->belongsTo(ProviderType::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}