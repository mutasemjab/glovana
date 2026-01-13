<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProviderSettlement extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'paid_at' => 'datetime',
    ];

    public function settlementCycle()
    {
        return $this->belongsTo(SettlementCycle::class);
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }
}