<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppointmentSettlement extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function settlementCycle()
    {
        return $this->belongsTo(SettlementCycle::class);
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }
}