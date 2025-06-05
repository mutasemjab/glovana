<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

      protected $guarded = [];
      
    /**
     * Get the user associated with the order.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the driver associated with the order.
     */
    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    /**
     * Get the service associated with the order.
     */
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Get the order status text.
     *
     * @return string
     */
    public function getStatusText()
    {
        switch ($this->status) {
            case 1:
                return __('messages.Pending');
            case 2:
                return __('messages.Driver_Accepted');
            case 3:
                return __('messages.Driver_Going_To_User');
            case 4:
                return __('messages.User_With_Driver');
            case 5:
                return __('messages.Delivered');
            case 6:
                return __('messages.User_Cancelled');
            case 7:
                return __('messages.Driver_Cancelled');
            default:
                return __('messages.Unknown');
        }
    }

    /**
     * Get the order status class for UI display.
     *
     * @return string
     */
    public function getStatusClass()
    {
        switch ($this->status) {
            case 1:
                return 'warning';
            case 2:
                return 'info';
            case 3:
                return 'primary';
            case 4:
                return 'primary';
            case 5:
                return 'success';
            case 6:
                return 'danger';
            case 7:
                return 'danger';
            default:
                return 'secondary';
        }
    }

    /**
     * Get the payment method text.
     *
     * @return string
     */
    public function getPaymentMethodText()
    {
        switch ($this->payment_method) {
            case 1:
                return __('messages.Cash');
            case 2:
                return __('messages.Visa');
            case 3:
                return __('messages.Wallet');
            default:
                return __('messages.Unknown');
        }
    }

    /**
     * Get the payment status text.
     *
     * @return string
     */
    public function getPaymentStatusText()
    {
        return $this->status_payment == 1 
               ? __('messages.Pending') 
               : __('messages.Paid');
    }

    /**
     * Get the payment status class.
     *
     * @return string
     */
    public function getPaymentStatusClass()
    {
        return $this->status_payment == 1 
               ? 'warning' 
               : 'success';
    }

    /**
     * Check if the order is cancelled.
     *
     * @return bool
     */
    public function isCancelled()
    {
        return in_array($this->status, [6, 7]);
    }

    /**
     * Check if the order is completed.
     *
     * @return bool
     */
    public function isCompleted()
    {
        return $this->status == 5;
    }

    /**
     * Check if the order is in progress.
     *
     * @return bool
     */
    public function isInProgress()
    {
        return in_array($this->status, [2, 3, 4]);
    }

    /**
     * Get the distance between pickup and drop-off locations in kilometers.
     * Using the Haversine formula to calculate the distance.
     *
     * @return float
     */
    public function getDistance()
    {
        $earthRadius = 6371; // Radius of the earth in km

        $latFrom = deg2rad($this->pick_lat);
        $lonFrom = deg2rad($this->pick_lng);
        $latTo = deg2rad($this->drop_lat);
        $lonTo = deg2rad($this->drop_lng);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) + cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
        $distance = $angle * $earthRadius;

        // Return distance with 2 decimal places
        return round($distance, 2);
    }

    /**
     * Get the formatted discount value.
     *
     * @return string
     */
    public function getFormattedDiscount()
    {
        if (!$this->discount_value) {
            return '0';
        }
        
        return $this->discount_value;
    }

    /**
     * Get the discount percentage based on original price.
     *
     * @return float
     */
    public function getDiscountPercentage()
    {
        if (!$this->discount_value || $this->total_price_before_discount == 0) {
            return 0;
        }
        
        $percentage = ($this->discount_value / $this->total_price_before_discount) * 100;
        return round($percentage, 1);
    }
}
