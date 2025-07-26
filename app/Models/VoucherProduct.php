<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoucherProduct extends Model
{
    use HasFactory;

     protected $guarded=[];

       public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function noteVoucher()
    {
        return $this->belongsTo(NoteVoucher::class);
    }

    public function isLowStock()
    {
        $minimumQuantity = Setting::getValue('minimum_to_notify_me_for_quantity_products', 2);
        return $this->quantity <= $minimumQuantity;
    }

    public function getLowStockClass()
    {
        return $this->isLowStock() ? 'low-stock' : '';
    }

    public static function getLowStockProducts()
    {
        $minimumQuantity = Setting::getValue('minimum_to_notify_me_for_quantity_products', 2);
        return self::with(['product', 'noteVoucher'])
                   ->where('quantity', '<=', $minimumQuantity)
                   ->get();
    }

    public static function getLowStockCount()
    {
        $minimumQuantity = Setting::getValue('minimum_to_notify_me_for_quantity_products', 2);
        return self::where('quantity', '<=', $minimumQuantity)->count();
    }

    public static function getLowStockByProduct()
    {
        $minimumQuantity = Setting::getValue('minimum_to_notify_me_for_quantity_products', 2);
        return self::with(['product', 'noteVoucher'])
                   ->where('quantity', '<=', $minimumQuantity)
                   ->get()
                   ->groupBy('product_id');
    }

    public function getStatusBadgeAttribute()
    {
        if ($this->quantity == 0) {
            return '<span class="badge bg-danger">' . __('messages.out_of_stock') . '</span>';
        } elseif ($this->isLowStock()) {
            return '<span class="badge bg-warning">' . __('messages.low_stock') . '</span>';
        } else {
            return '<span class="badge bg-success">' . __('messages.in_stock') . '</span>';
        }
    }
}
