<?php

namespace App\Jobs;

use App\Http\Controllers\Admin\FCMController;
use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutoCancelAppointment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $appointmentId;
    protected $userId;
    protected $providerId;
    protected $timeoutMinutes;

    public function __construct($appointmentId, $userId, $providerId, $timeoutMinutes = 1)
    {
        $this->appointmentId = $appointmentId;
        $this->userId = $userId;
        $this->providerId = $providerId;
        $this->timeoutMinutes = $timeoutMinutes;
    }

    public function handle()
    {
        try {
            // Reload appointment to get current status
            $appointment = Appointment::find($this->appointmentId);

            if (!$appointment) {
                Log::warning("Auto-cancel: Appointment #{$this->appointmentId} not found");
                return;
            }

            // Only cancel if still in Pending status (status = 1)
            if ($appointment->appointment_status == 1) {
                DB::beginTransaction();

                // Update appointment to Canceled
                $appointment->appointment_status = 5;
                $appointment->reason_of_cancel = 'Automatically canceled - Provider did not respond within ' . $this->timeoutMinutes . ' minute(s)';
                $appointment->canceled_at = now();
                $appointment->save();

                DB::commit();

                // Send notification to user
                $title = "Appointment Canceled";
                $body = "Your appointment #{$appointment->number} was automatically canceled because the provider was not available to accept it.";

                try {
                    FCMController::sendMessageToUser($title, $body, $this->userId);
                    Log::info("Auto-cancel notification sent to user ID: {$this->userId} for appointment #{$this->appointmentId}");
                } catch (\Exception $e) {
                    Log::error("Failed to send auto-cancel notification to user: " . $e->getMessage());
                }

                Log::info("Appointment #{$this->appointmentId} auto-canceled after {$this->timeoutMinutes} minute(s)");
            } else {
                Log::info("Appointment #{$this->appointmentId} was accepted before auto-cancel timeout. Current status: {$appointment->appointment_status}");
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Auto-cancel error for appointment #{$this->appointmentId}: " . $e->getMessage());
        }
    }
}