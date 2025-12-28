<?php

namespace App\Jobs;

use App\Http\Controllers\Admin\FCMController;
use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendProviderStatusReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $appointmentId;
    protected $providerId;

    public function __construct($appointmentId, $providerId)
    {
        $this->appointmentId = $appointmentId;
        $this->providerId = $providerId;
    }

    public function handle()
    {
        try {
            $appointment = Appointment::find($this->appointmentId);

            if (!$appointment) {
                Log::warning("Reminder: Appointment #{$this->appointmentId} not found");
                return;
            }

            // Check if still in Accepted status (status = 2)
            if ($appointment->appointment_status == 2) {
                $title = "⚠️ Action Required - Update Appointment Status";
                $body = "Please update appointment #{$appointment->number} status to 'On The Way' or the appointment will be automatically canceled.";

                FCMController::sendMessageToProvider($title, $body, $this->providerId);
                
                Log::info("Status reminder sent to provider ID: {$this->providerId} for appointment #{$this->appointmentId}");
            } else {
                Log::info("Appointment #{$this->appointmentId} status already updated to {$appointment->appointment_status}");
            }
        } catch (\Exception $e) {
            Log::error("Reminder job error for appointment #{$this->appointmentId}: " . $e->getMessage());
        }
    }
}