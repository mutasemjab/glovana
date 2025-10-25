<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiscountService extends Model
{
    use HasFactory;

    protected $guarded=[];

     public function discount()
    {
        return $this->belongsTo(Discount::class);
    }

    /**
     * Get the service
     */
    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
