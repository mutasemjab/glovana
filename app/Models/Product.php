<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

class Product extends Model
{
    use HasFactory;
      
    protected $guarded = [];

     public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function ratings()
    {
        return $this->hasMany(ProductRating::class);
    }

     protected $appends = ['name', 'description', 'specification'];

    public function getNameAttribute()
    {
        $lang = request()->header('Accept-Language') ?? App::getLocale();
        return $lang === 'ar' ? $this->name_ar : $this->name_en;
    }

    public function getDescriptionAttribute()
    {
        $lang = request()->header('Accept-Language') ?? App::getLocale();
        return $lang === 'ar' ? $this->description_ar : $this->description_en;
    }

    public function getSpecificationAttribute()
    {
        $lang = request()->header('Accept-Language') ?? App::getLocale();
        return $lang === 'ar' ? $this->specification_ar : $this->specification_en;
    }

}
