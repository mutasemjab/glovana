<?php

namespace App\Console\Commands;

use App\Http\Controllers\Admin\FCMController;
use App\Models\Appointment;
use App\Models\ProviderType;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckHourlyAppointmentsStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'appointments:check-hourly-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check hourly appointments that are accepted but not moved to "On The Way" status within the appointment hour';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            $now = Carbon::now();
            $currentHour = $now->format('Y-m-d H:00:00');
            $nextHour = $now->copy()->addHour()->format('Y-m-d H:00:00');

            Log::info("Starting hourly appointments check at {$now}");

            // Get all appointments that:
            // 1. Are hourly bookings
            // 2. Status is "Accepted" (2)
            // 3. Date is today
            // 4. Appointment hour is current hour (appointment time has started)
            $appointments = Appointment::with(['providerType.type', 'providerType.provider', 'user'])
                ->whereHas('providerType.type', function($query) {
                    $query->where('booking_type', 'hourly');
                })
                ->where('appointment_status', 2) // Accepted
                ->whereDate('date', $now->toDateString()) // Today
                ->where('date', '>=', $currentHour) // Current hour or later
                ->where('date', '<', $nextHour) // Before next hour
                ->get();

            if ($appointments->isEmpty()) {
                $this->info('No hourly appointments need status check at this time.');
                Log::info('No hourly appointments found needing status check');
                return 0;
            }

            $this->info("Found {$appointments->count()} hourly appointment(s) to check");

            foreach ($appointments as $appointment) {
                $appointmentTime = Carbon::parse($appointment->date);
                $minutesSinceAppointment = $now->diffInMinutes($appointmentTime, false);

                // If appointment time has passed (negative value means time has passed)
                if ($minutesSinceAppointment <= 0) {
                    $minutesPassed = abs($minutesSinceAppointment);

                    // Send reminder if appointment time has started
                    $this->sendProviderReminder($appointment, $minutesPassed);
                    
                    $this->info("Sent reminder for appointment #{$appointment->number} (started {$minutesPassed} minutes ago)");
                } else {
                    // Appointment hasn't started yet
                    $this->info("Appointment #{$appointment->number} hasn't started yet (starts in {$minutesSinceAppointment} minutes)");
                }
            }

            Log::info("Completed hourly appointments check - processed {$appointments->count()} appointments");
            
            return 0;

        } catch (\Exception $e) {
            $this->error("Error checking hourly appointments: " . $e->getMessage());
            Log::error("Error in CheckHourlyAppointmentsStatus command: " . $e->getMessage());
            return 1;
        }
    }

    /**
     * Send reminder notification to provider
     */
    private function sendProviderReminder($appointment, $minutesPassed)
    {
        try {
            $provider = $appointment->providerType->provider;
            $userName = $appointment->user->name ?? 'Customer';

            $title = "⚠️ Appointment Time Started - Action Required";
            $body = "Appointment #{$appointment->number} with {$userName} started {$minutesPassed} minute(s) ago. Please update status to 'On The Way' immediately.";

            // Save notification to database
            \App\Models\Notification::create([
                'title' => $title,
                'body' => $body,
                'type' => 2, // provider type
                'provider_id' => $provider->id,
            ]);

            // Send FCM notification
            FCMController::sendMessageToProvider($title, $body, $provider->id);

            Log::info("Hourly appointment reminder sent to provider ID: {$provider->id} for appointment #{$appointment->id}");

        } catch (\Exception $e) {
            Log::error("Failed to send hourly appointment reminder: " . $e->getMessage());
        }
    }
}