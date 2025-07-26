<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
class FineDiscount extends Model
{
    use HasFactory,LogsActivity;

      protected $guarded=[];

      protected $casts = [
        'applied_at' => 'datetime',
        'due_date' => 'datetime',
        'amount' => 'double',
        'percentage' => 'double',
        'original_amount' => 'double'
    ];


        public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll() // Log all attributes since you're using $guarded = []
            ->logOnlyDirty() // Only log changed attributes
            ->dontSubmitEmptyLogs() // Don't log if nothing changed
            ->dontLogIfAttributesChangedOnly(['updated_at']) // Don't log if only updated_at changed
            ->setDescriptionForEvent(fn(string $eventName) => "fine {$eventName}")
            ->useLogName('fine'); // Custom log name for filtering
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function walletTransaction()
    {
        return $this->belongsTo(WalletTransaction::class);
    }

    // Accessors
    public function getCategoryTextAttribute()
    {
        return match($this->category) {
            1 => __('messages.automatic'),
            2 => __('messages.manual'),
            default => __('messages.unknown')
        };
    }

    public function getStatusTextAttribute()
    {
        return match($this->status) {
            1 => __('messages.pending'),
            2 => __('messages.applied'),
            3 => __('messages.reversed'),
            4 => __('messages.failed'),
            default => __('messages.unknown')
        };
    }

    public function getEntityTypeAttribute()
    {
        if ($this->user_id) return 'user';
        if ($this->provider_id) return 'provider';
        return 'unknown';
    }

    public function getEntityNameAttribute()
    {
        if ($this->user_id && $this->user) {
            return $this->user->name;
        }
        if ($this->provider_id && $this->provider) {
            return $this->provider->name_of_manager;
        }
        return __('messages.unknown');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 1);
    }

    public function scopeApplied($query)
    {
        return $query->where('status', 2);
    }

    public function scopeAutomatic($query)
    {
        return $query->where('category', 1);
    }

    public function scopeManual($query)
    {
        return $query->where('category', 2);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForProvider($query, $providerId)
    {
        return $query->where('provider_id', $providerId);
    }

    public function scopeDue($query)
    {
        return $query->where('status', 1)
                    ->where(function($q) {
                        $q->whereNull('due_date')
                          ->orWhere('due_date', '<=', Carbon::now());
                    });
    }

    // Methods
    public function canBeApplied()
    {
        if ($this->status != 1) return false;
        
        if ($this->due_date && $this->due_date > Carbon::now()) {
            return false;
        }

        return true;
    }

    public function apply()
    {
        if (!$this->canBeApplied()) {
            return false;
        }

        \DB::beginTransaction();
        try {
            // Create wallet transaction (fine = withdrawal)
            $transaction = WalletTransaction::create([
                'user_id' => $this->user_id,
                'provider_id' => $this->provider_id,
                'admin_id' => $this->admin_id,
                'amount' => $this->amount,
                'type_of_transaction' => 2, // withdrawal
                'note' => $this->reason . ($this->notes ? ' - ' . $this->notes : '')
            ]);

            // Update entity balance (deduct amount)
            if ($this->user_id) {
                $user = $this->user;
                $user->balance -= $this->amount;
                $user->save();
            } elseif ($this->provider_id) {
                $provider = $this->provider;
                $provider->balance -= $this->amount;
                $provider->save();
            }

            // Update fine status
            $this->update([
                'status' => 2, // Applied
                'applied_at' => Carbon::now(),
                'wallet_transaction_id' => $transaction->id
            ]);

            // Update appointment if related
            if ($this->appointment_id) {
                $this->appointment->update([
                    'fine_amount' => $this->amount,
                    'fine_applied' => 1
                ]);
            }

            \DB::commit();
            return true;
        } catch (\Exception $e) {
            \DB::rollback();
            $this->update(['status' => 4]); // Failed
            return false;
        }
    }

    public function reverse()
    {
        if ($this->status != 2) return false;

        \DB::beginTransaction();
        try {
            // Create reverse transaction (add money back)
            $transaction = WalletTransaction::create([
                'user_id' => $this->user_id,
                'provider_id' => $this->provider_id,
                'admin_id' => auth()->guard('admin')->id(),
                'amount' => $this->amount,
                'type_of_transaction' => 1, // add money back
                'note' => __('messages.reversal_of') . ' ' . $this->reason
            ]);

            // Update entity balance (add money back)
            if ($this->user_id) {
                $user = $this->user;
                $user->balance += $this->amount;
                $user->save();
            } elseif ($this->provider_id) {
                $provider = $this->provider;
                $provider->balance += $this->amount;
                $provider->save();
            }

            // Update status
            $this->update(['status' => 3]); // Reversed

            // Update appointment if related
            if ($this->appointment_id) {
                $this->appointment->update([
                    'fine_amount' => 0,
                    'fine_applied' => 2
                ]);
            }

            \DB::commit();
            return true;
        } catch (\Exception $e) {
            \DB::rollback();
            return false;
        }
    }

    // Static methods for creating fines
    public static function createLateCancellationFine($appointment)
    {
        $settings = FineSetting::getAllSettings();
        $finePercentage = (float) ($settings['fine_percentage'] ?? 25);
        $minimumFine = (float) ($settings['minimum_fine_amount'] ?? 5);
        $maximumFine = (float) ($settings['maximum_fine_amount'] ?? 100);
        
        $fineAmount = ($appointment->total_prices * $finePercentage) / 100;
        $fineAmount = max($minimumFine, min($maximumFine, $fineAmount));

        return self::create([
            'user_id' => $appointment->user_id,
            'appointment_id' => $appointment->id,
            'category' => 1, // Automatic
            'amount' => $fineAmount,
            'percentage' => $finePercentage,
            'original_amount' => $appointment->total_prices,
            'reason' => __('messages.late_cancellation_fine'),
            'status' => 1 // Pending
        ]);
    }

    public static function createManualFine($entityType, $entityId, $amount, $reason, $notes = null)
    {
        $data = [
            'category' => 2, // Manual
            'amount' => $amount,
            'reason' => $reason,
            'notes' => $notes,
            'status' => 1, // Pending
            'admin_id' => auth()->guard('admin')->id()
        ];

        if ($entityType === 'user') {
            $data['user_id'] = $entityId;
        } elseif ($entityType === 'provider') {
            $data['provider_id'] = $entityId;
        }

        return self::create($data);
    }
}
