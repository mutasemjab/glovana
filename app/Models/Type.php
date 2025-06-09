<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

class Type extends Model
{
    use HasFactory;
    
    protected $guarded = [];

    protected $appends = ['name']; 

    public function getNameAttribute()
    {
        $lang = request()->header('Accept-Language') ?? App::getLocale();

        return $lang === 'ar' ? $this->name_ar : $this->name_en;
    }
}
