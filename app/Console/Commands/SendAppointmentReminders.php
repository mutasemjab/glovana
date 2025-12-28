<?php

namespace App\Console\Commands;

use App\Http\Controllers\Admin\FCMController as AdminFCMController;
use Illuminate\Console\Command;
use App\Models\Appointment;
use App\Http\Controllers\FCMController;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SendAppointmentReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'appointments:send-reminders {--test : Run in test mode without sending notifications}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send reminder notifications to users and providers 2 hours before appointments';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $isTestMode = $this->option('test');
        
        $this->info('Starting appointment reminder process...');
        
        try {
            // Get appointments that are 2 hours away from now
            $reminderTime = Carbon::now()->addHours(2);
            
            // Find appointments within the next 2 hours (with 30-minute window)
            $startTime = $reminderTime->copy()->subMinutes(30);
            $endTime = $reminderTime->copy()->addMinutes(30);
            
            $appointments = Appointment::with([
                'user:id,name,phone,email,fcm_token',
                'providerType.provider:id,name_of_manager,phone,email,fcm_token',
                'providerType:id,name',
                'address'
            ])
            ->whereBetween('date', [$startTime, $endTime])
            ->where('appointment_status',2) // Pending or Accepted
            ->get();

            if ($appointments->isEmpty()) {
                $this->info('No appointments found requiring reminders.');
                return Command::SUCCESS;
            }

            $this->info("Found {$appointments->count()} appointments requiring reminders.");

            $successCount = 0;
            $errorCount = 0;

            foreach ($appointments as $appointment) {
                try {
                    if ($isTestMode) {
                        $this->displayTestInfo($appointment);
                    } else {
                        $this->sendReminders($appointment);
                        
                    }
                    
                    $successCount++;
                    
                } catch (\Exception $e) {
                    $errorCount++;
                    $this->error("Failed to send reminder for appointment #{$appointment->number}: " . $e->getMessage());
                    Log::error("Appointment reminder failed for ID {$appointment->id}: " . $e->getMessage());
                }
            }

            if ($isTestMode) {
                $this->info("Test mode completed. Would send reminders for {$successCount} appointments.");
            } else {
                $this->info("Reminder process completed successfully!");
                $this->info("Sent: {$successCount} reminders");
                if ($errorCount > 0) {
                    $this->error("Failed: {$errorCount} reminders");
                }
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Error in reminder process: ' . $e->getMessage());
            Log::error('Appointment reminder command failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Send reminder notifications for an appointment
     */
    private function sendReminders($appointment)
    {
        $appointmentTime = Carbon::parse($appointment->date);
        $timeUntil = Carbon::now()->diffInMinutes($appointmentTime);
        
        // Send reminder to user
        if ($appointment->user && $appointment->user->fcm_token) {
            $this->sendUserReminder($appointment, $timeUntil);
        }

        // Send reminder to provider
        if ($appointment->providerType && 
            $appointment->providerType->provider && 
            $appointment->providerType->provider->fcm_token) {
            $this->sendProviderReminder($appointment, $timeUntil);
        }

        $this->info("✓ Sent reminders for appointment #{$appointment->number}");
    }

    /**
     * Send reminder to user
     */
    private function sendUserReminder($appointment, $timeUntil)
    {
        $hours = intval($timeUntil / 60);
        $minutes = $timeUntil % 60;
        
        $timeText = $hours > 0 ? "{$hours} hour(s)" : "{$minutes} minute(s)";
        if ($hours > 0 && $minutes > 0) {
            $timeText = "{$hours} hour(s) and {$minutes} minute(s)";
        }

        $title = "Appointment Reminder";
        $body = "Your appointment #{$appointment->number} with {$appointment->providerType->name} is scheduled in {$timeText}.";
        
        // Add address if available
        if ($appointment->address) {
            $body .= " Location: {$appointment->address->address}";
        }

        $data = [
            'type' => 'appointment_reminder',
            'appointment_id' => $appointment->id,
            'appointment_number' => $appointment->number,
            'time_until' => $timeUntil,
            'appointment_date' => $appointment->date->toISOString()
        ];

        AdminFCMController::sendMessageToUser($title, $body, $appointment->user_id, $data);
        
        Log::info("User reminder sent for appointment #{$appointment->number} to user ID: {$appointment->user_id}");
    }

    /**
     * Send reminder to provider
     */
    private function sendProviderReminder($appointment, $timeUntil)
    {
        $hours = intval($timeUntil / 60);
        $minutes = $timeUntil % 60;
        
        $timeText = $hours > 0 ? "{$hours} hour(s)" : "{$minutes} minute(s)";
        if ($hours > 0 && $minutes > 0) {
            $timeText = "{$hours} hour(s) and {$minutes} minute(s)";
        }

        $title = "Appointment Reminder";
        $body = "You have an appointment #{$appointment->number} with {$appointment->user->name} in {$timeText}.";
        
        // Add customer phone for provider
        if ($appointment->user->phone) {
            $body .= " Customer: {$appointment->user->phone}";
        }

        $data = [
            'type' => 'appointment_reminder',
            'appointment_id' => $appointment->id,
            'appointment_number' => $appointment->number,
            'customer_name' => $appointment->user->name,
            'customer_phone' => $appointment->user->phone,
            'time_until' => $timeUntil,
            'appointment_date' => $appointment->date->toISOString()
        ];

        AdminFCMController::sendMessageToProvider($title, $body, $appointment->providerType->provider->id, $data);
        
        Log::info("Provider reminder sent for appointment #{$appointment->number} to provider ID: {$appointment->providerType->provider->id}");
    }

    /**
     * Display test information without sending notifications
     */
    private function displayTestInfo($appointment)
    {
        $appointmentTime = Carbon::parse($appointment->date);
        $timeUntil = Carbon::now()->diffInMinutes($appointmentTime);
        
        $this->line("🔔 Test Mode - Appointment #{$appointment->number}");
        $this->line("   Date: {$appointment->date}");
        $this->line("   Time until: {$timeUntil} minutes");
        $this->line("   User: {$appointment->user->name} ({$appointment->user->phone})");
        $this->line("   Provider: {$appointment->providerType->provider->name_of_manager}");
        $this->line("   Service: {$appointment->providerType->name}");
        $this->line("   Status: " . $this->getStatusText($appointment->appointment_status));
        
        if ($appointment->address) {
            $this->line("   Address: {$appointment->address->address}");
        }
        
        $this->line("   ---");
    }

    /**
     * Get appointment status text
     */
    private function getStatusText($status)
    {
        $statuses = [
            1 => 'Pending',
            2 => 'Accepted', 
            3 => 'On The Way',
            4 => 'Delivered',
            5 => 'Canceled'
        ];

        return $statuses[$status] ?? 'Unknown';
    }

   
}