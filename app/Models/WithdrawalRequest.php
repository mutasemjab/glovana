<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class WithdrawalRequest extends Model
{
    use HasFactory,LogsActivity;

    
     protected $guarded = [];
    
     public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll() // Log all attributes since you're using $guarded = []
            ->logOnlyDirty() // Only log changed attributes
            ->dontSubmitEmptyLogs() // Don't log if nothing changed
            ->dontLogIfAttributesChangedOnly(['updated_at']) // Don't log if only updated_at changed
            ->setDescriptionForEvent(fn(string $eventName) => "WithdrawalRequest {$eventName}")
            ->useLogName('WithdrawalRequest'); // Custom log name for filtering
    }

     public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }
    
    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}
