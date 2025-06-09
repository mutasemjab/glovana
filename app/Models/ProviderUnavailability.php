<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProviderUnavailability extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $dates = ['unavailable_date'];

      public function providerType()
    {
        return $this->belongsTo(ProviderType::class);
    }

    public function getFormattedTimeAttribute()
    {
        if ($this->start_time && $this->end_time) {
            return $this->start_time . ' - ' . $this->end_time;
        }
        return 'Full Day';
    }

    public function getUnavailableTypeAttribute()
    {
        return ($this->start_time && $this->end_time) ? 'Partial' : 'Full Day';
    }
}