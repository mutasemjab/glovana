<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\FineDiscount;
use App\Models\FineSetting;
use App\Models\Provider;
use App\Models\User;
use App\Models\WalletTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


class AppointmentService
{
    /**
     * Cancel appointment and apply fine if necessary
     */
    public function cancelAppointment(Appointment $appointment, $reason, $canceledBy = 'user')
    {
        DB::beginTransaction();
        try {
            $now = Carbon::now();
            
            // Update appointment
            $appointment->update([
                'appointment_status' => 5, // Canceled
                'reason_of_cancel' => $reason,
                'canceled_at' => $now
            ]);

            // Check if fine should be applied (only for user cancellations)
            if ($canceledBy === 'user' && $appointment->user_id) {
                $this->checkAndApplyLateCancellationFine($appointment, $now);
            }
            
            // Check if fine should be applied (only for provider cancellations)
            if ($canceledBy === 'provider' && $appointment->provider_id) {
                $this->checkAndApplyProviderCancellationFine($appointment, $now);
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Check if late cancellation fine should be applied for users
     */
    protected function checkAndApplyLateCancellationFine(Appointment $appointment, Carbon $canceledAt)
    {
        $settings = FineSetting::getAllSettings();
        $lateCancellationHours = (int) ($settings['late_cancellation_hours'] ?? 24);
        $autoApplyFines = (int) ($settings['auto_apply_fines'] ?? 1);
        $finePercentage = (float) ($settings['fine_percentage'] ?? 10);

        // Calculate time difference
        $appointmentTime = Carbon::parse($appointment->date);
        $hoursUntilAppointment = $canceledAt->diffInHours($appointmentTime, false);

        // If cancellation is within the late cancellation window
        if ($hoursUntilAppointment > 0 && $hoursUntilAppointment <= $lateCancellationHours) {
            $originalAmount = $appointment->total_amount ?? 0;
            $fineAmount = ($originalAmount * $finePercentage) / 100;

            // Create fine record
            $fine = FineDiscount::create([
                'user_id' => $appointment->user_id,
                'appointment_id' => $appointment->id,
                'category' => 1, // Automatic
                'amount' => $fineAmount,
                'percentage' => $finePercentage,
                'original_amount' => $originalAmount,
                'status' => $autoApplyFines ? 2 : 1, // Applied or Pending
                'reason' => "Late cancellation fine - canceled {$hoursUntilAppointment} hours before appointment",
                'applied_at' => $autoApplyFines ? $canceledAt : null,
                'due_date' => $autoApplyFines ? null : $canceledAt
            ]);

            // If auto-apply is enabled, deduct from user balance
            if ($autoApplyFines && $appointment->user) {
                $this->applyFineToUserBalance($appointment->user, $fine);
            }

            return $fine;
        }

        return null;
    }

    /**
     * Check if cancellation fine should be applied for providers
     */
    protected function checkAndApplyProviderCancellationFine(Appointment $appointment, Carbon $canceledAt)
    {
        $settings = FineSetting::getAllSettings();
        $providerCancellationHours = (int) ($settings['provider_cancellation_hours'] ?? 2);
        $autoApplyFines = (int) ($settings['auto_apply_fines'] ?? 1);
        $providerFinePercentage = (float) ($settings['provider_fine_percentage'] ?? 15);

        // Calculate time difference
        $appointmentTime = Carbon::parse($appointment->date);
        $hoursUntilAppointment = $canceledAt->diffInHours($appointmentTime, false);

        // If provider cancellation is within the cancellation window
        if ($hoursUntilAppointment > 0 && $hoursUntilAppointment <= $providerCancellationHours) {
            $originalAmount = $appointment->total_amount ?? 0;
            $fineAmount = ($originalAmount * $providerFinePercentage) / 100;

            // Create fine record for provider
            $fine = FineDiscount::create([
                'provider_id' => $appointment->provider_id,
                'appointment_id' => $appointment->id,
                'category' => 1, // Automatic
                'amount' => $fineAmount,
                'percentage' => $providerFinePercentage,
                'original_amount' => $originalAmount,
                'status' => $autoApplyFines ? 2 : 1, // Applied or Pending
                'reason' => "Provider late cancellation fine - canceled {$hoursUntilAppointment} hours before appointment",
                'applied_at' => $autoApplyFines ? $canceledAt : null,
                'due_date' => $autoApplyFines ? null : $canceledAt
            ]);

            // If auto-apply is enabled, deduct from provider balance
            if ($autoApplyFines && $appointment->provider) {
                $this->applyFineToProviderBalance($appointment->provider, $fine);
            }

            return $fine;
        }

        return null;
    }

    /**
     * Apply fine to user balance
     */
    protected function applyFineToUserBalance($user, $fine)
    {
        // Deduct fine amount from user balance
        $user->decrement('balance', $fine->amount);

        // Create wallet transaction record
        $transaction = WalletTransaction::create([
            'user_id' => $user->id,
            'amount' => $fine->amount,
            'type_of_transaction' => 2, // 2 = withdrawal
            'note' => 'Late cancellation fine - Appointment ID: ' . $fine->appointment_id
        ]);

        // Update fine with transaction reference
        $fine->update(['wallet_transaction_id' => $transaction->id]);

        return $transaction;
    }

    /**
     * Apply fine to provider balance
     */
    protected function applyFineToProviderBalance($provider, $fine)
    {
        // Deduct fine amount from provider balance
        $provider->decrement('balance', $fine->amount);

        // Create wallet transaction record
        $transaction = WalletTransaction::create([
            'provider_id' => $provider->id,
            'amount' => $fine->amount,
            'type_of_transaction' => 2, // 2 = withdrawal
            'note' => 'Provider late cancellation fine - Appointment ID: ' . $fine->appointment_id
        ]);

        // Update fine with transaction reference
        $fine->update(['wallet_transaction_id' => $transaction->id]);

        return $transaction;
    }

    /**
     * Process pending fines
     */
    public static function processPendingFines()
    {
        $pendingFines = FineDiscount::where('status', 1)
            ->where('due_date', '<=', Carbon::now())
            ->get();
            
        $processed = 0;
        $failed = 0;

        foreach ($pendingFines as $fine) {
            try {
                DB::beginTransaction();
                
                if ($fine->user_id) {
                    $user = User::find($fine->user_id);
                    if ($user) {
                        (new self())->applyFineToUserBalance($user, $fine);
                        $fine->update([
                            'status' => 2, // Applied
                            'applied_at' => Carbon::now()
                        ]);
                        $processed++;
                    } else {
                        $fine->update(['status' => 4]); // Failed
                        $failed++;
                    }
                } elseif ($fine->provider_id) {
                    $provider = Provider::find($fine->provider_id);
                    if ($provider) {
                        (new self())->applyFineToProviderBalance($provider, $fine);
                        $fine->update([
                            'status' => 2, // Applied
                            'applied_at' => Carbon::now()
                        ]);
                        $processed++;
                    } else {
                        $fine->update(['status' => 4]); // Failed
                        $failed++;
                    }
                }
                
                DB::commit();
            } catch (\Exception $e) {
                DB::rollback();
                $fine->update(['status' => 4]); // Failed
                $failed++;
            }
        }

        return [
            'processed' => $processed,
            'failed' => $failed,
            'total' => $pendingFines->count()
        ];
    }
}