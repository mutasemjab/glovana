<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
class Product extends Model
{
    use HasFactory,LogsActivity;
      
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

    protected $appends = ['name', 'description', 'specification','is_favourite'];


      public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll() // Log all attributes since you're using $guarded = []
            ->logOnlyDirty() // Only log changed attributes
            ->dontSubmitEmptyLogs() // Don't log if nothing changed
            ->dontLogIfAttributesChangedOnly(['updated_at']) // Don't log if only updated_at changed
            ->setDescriptionForEvent(fn(string $eventName) => "Products {$eventName}")
            ->useLogName('products'); // Custom log name for filtering
    }

    public function getIsFavouriteAttribute()
    {
        if (!auth()->check()) {
            return 0;
        }
        
        return DB::table('product_favourites')
            ->where('product_id', $this->id)
            ->where('user_id', auth()->id())
            ->exists() ? 1 : 0;
    }

    // Add the relationship in Product model if not already exists
    public function favouritedBy()
    {
        return $this->belongsToMany(User::class, 'product_favourites', 'product_id', 'user_id');
    }

    
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
