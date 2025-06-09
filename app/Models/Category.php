<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
class Category extends Model
{
    use HasFactory;
    protected $guarded = [];

     public function products()
    {
        return $this->hasMany(Product::class);
    }

    protected $appends = ['name']; // Include in JSON output

    public function getNameAttribute()
    {
        $lang = request()->header('Accept-Language') ?? App::getLocale();

        return $lang === 'ar' ? $this->name_ar : $this->name_en;
    }
}
