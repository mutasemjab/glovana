<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\FineDiscount;
use App\Models\FineSetting;
use Carbon\Carbon;

class AppointmentService
{
    /**
     * Cancel appointment and apply fine if necessary
     */
    public function cancelAppointment(Appointment $appointment, $reason, $canceledBy = 'user')
    {
        \DB::beginTransaction();
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

            \DB::commit();
            return true;
        } catch (\Exception $e) {
            \DB::rollback();
            throw $e;
        }
    }

    /**
     * Check if late cancellation fine should be applied
     */
    protected function checkAndApplyLateCancellationFine(Appointment $appointment, Carbon $canceledAt)
    {
        $settings = FineSetting::getAllSettings();
        $lateCancellationHours = (int) ($settings['late_cancellation_hours'] ?? 24);
        $autoApplyFines = (int) ($settings['auto_apply_fines'] ?? 1);

        // Calculate time difference
        $appointmentTime = Carbon::parse($appointment->date);
        $hoursUntilAppointment = $canceledAt->diffInHours($appointmentTime, false);

        // If cancellation is within the restricted time frame
        if ($hoursUntilAppointment <= $lateCancellationHours && $hoursUntilAppointment >= 0) {
            // Create the fine
            $fine = Fine::createLateCancellationFine($appointment);

            // Auto-apply if enabled
            if ($autoApplyFines == 1) {
                $fine->apply();
            }

            return $fine;
        }

        return null;
    }

    /**
     * Get cancellation info for an appointment
     */
    public function getCancellationInfo(Appointment $appointment)
    {
        $settings = FineSetting::getAllSettings();
        $lateCancellationHours = (int) ($settings['late_cancellation_hours'] ?? 24);
        $finePercentage = (float) ($settings['fine_percentage'] ?? 25);
        $minimumFine = (float) ($settings['minimum_fine_amount'] ?? 5);
        $maximumFine = (float) ($settings['maximum_fine_amount'] ?? 100);

        $now = Carbon::now();
        $appointmentTime = Carbon::parse($appointment->date);
        $hoursUntilAppointment = $now->diffInHours($appointmentTime, false);

        $willIncurFine = $hoursUntilAppointment <= $lateCancellationHours && $hoursUntilAppointment >= 0;
        
        $fineAmount = 0;
        if ($willIncurFine) {
            $fineAmount = ($appointment->total_prices * $finePercentage) / 100;
            $fineAmount = max($minimumFine, min($maximumFine, $fineAmount));
        }

        return [
            'will_incur_fine' => $willIncurFine,
            'hours_until_appointment' => max(0, $hoursUntilAppointment),
            'late_cancellation_threshold' => $lateCancellationHours,
            'fine_amount' => $fineAmount,
            'fine_percentage' => $finePercentage,
            'can_cancel_free' => $hoursUntilAppointment > $lateCancellationHours
        ];
    }

    /**
     * Process pending fines
     */
    public static function processPendingFines()
    {
        $pendingFines = FineDiscount::due()->get();
        $processed = 0;
        $failed = 0;

        foreach ($pendingFines as $fine) {
            if ($fine->apply()) {
                $processed++;
            } else {
                $failed++;
            }
        }

        return [
            'processed' => $processed,
            'failed' => $failed,
            'total' => $pendingFines->count()
        ];
    }

    /**
     * Calculate potential fine for appointment
     */
    public static function calculatePotentialFine($totalAmount)
    {
        $settings = FineSetting::getAllSettings();
        $finePercentage = (float) ($settings['fine_percentage'] ?? 25);
        $minimumFine = (float) ($settings['minimum_fine_amount'] ?? 5);
        $maximumFine = (float) ($settings['maximum_fine_amount'] ?? 100);
        
        $fineAmount = ($totalAmount * $finePercentage) / 100;
        return max($minimumFine, min($maximumFine, $fineAmount));
    }

    /**
     * Get user's fine history
     */
    public static function getUserFineHistory($userId, $limit = 10)
    {
        return FineDiscount::where('user_id', $userId)
                          ->with(['appointment'])
                          ->orderBy('created_at', 'desc')
                          ->limit($limit)
                          ->get();
    }

    /**
     * Get provider's fine history
     */
    public static function getProviderFineHistory($providerId, $limit = 10)
    {
        return FineDiscount::where('provider_id', $providerId)
                          ->with(['appointment'])
                          ->orderBy('created_at', 'desc')
                          ->limit($limit)
                          ->get();
    }
}