<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Passport\HasApiTokens;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Admin extends Authenticatable
{
    use HasFactory, HasRoles, HasApiTokens, LogsActivity;

    protected $table = "admins";
    protected $fillable = [
        'name', 'email', 'username', 'password', 'created_at', 'updated_at', 'added_by', 'updated_by', 'is_super'
    ];

    /**
     * Get the options for activity logging.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll() // Log all fillable attributes
            ->logOnlyDirty() // Only log changed attributes
            ->dontSubmitEmptyLogs() // Don't log if nothing changed
            ->dontLogIfAttributesChangedOnly(['updated_at']) // Don't log if only updated_at changed
            ->setDescriptionForEvent(fn(string $eventName) => "Admin {$eventName}")
            ->useLogName('admin'); // Custom log name
    }

    /**
     * Hide sensitive attributes from activity log
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
}