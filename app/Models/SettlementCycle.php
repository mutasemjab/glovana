<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SettlementCycle extends Model
{
    use HasFactory;

      protected $guarded = [];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'completed_at' => 'datetime',
    ];

    public function providerSettlements()
    {
        return $this->hasMany(ProviderSettlement::class);
    }

    public function appointmentSettlements()
    {
        return $this->hasMany(AppointmentSettlement::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
}
