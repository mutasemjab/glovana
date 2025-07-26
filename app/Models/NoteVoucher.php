<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class NoteVoucher extends Model
{
    use HasFactory, LogsActivity;

    protected $guarded = [];

    protected $casts = [
        'date_note_voucher' => 'date',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll() // Log all attributes since you're using $guarded = []
            ->logOnlyDirty() // Only log changed attributes
            ->dontSubmitEmptyLogs() // Don't log if nothing changed
            ->dontLogIfAttributesChangedOnly(['updated_at']) // Don't log if only updated_at changed
            ->setDescriptionForEvent(fn(string $eventName) => "NoteVoucher {$eventName}")
            ->useLogName('note_voucher'); // Custom log name for filtering
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function voucherProducts()
    {
        return $this->hasMany(VoucherProduct::class);
    }

    public function getTypeTextAttribute()
    {
        return $this->type == 1 ? __('messages.in') : __('messages.out');
    }

    public function getTypeClassAttribute()
    {
        return $this->type == 1 ? 'success' : 'danger';
    }
}