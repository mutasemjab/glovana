<?php

namespace App\Console\Commands;

use App\Http\Controllers\Admin\FCMController;
use App\Models\Appointment;
use App\Models\Notification;
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
    protected $description = 'Check appointment status reminders for accepted hourly bookings and appointments stuck on "User arrived to provider"';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            $now = Carbon::now();

            Log::info("Starting appointment status reminders check at {$now}");

            $acceptedHourlyCount = $this->checkAcceptedHourlyAppointments($now);
            $arrivedReminderCount = $this->checkUserArrivedAppointments($now);

            Log::info('Completed appointment status reminders check', [
                'accepted_hourly_checked' => $acceptedHourlyCount,
                'user_arrived_checked' => $arrivedReminderCount,
            ]);

            return 0;
        } catch (\Exception $e) {
            $this->error("Error checking hourly appointments: " . $e->getMessage());
            Log::error("Error in CheckHourlyAppointmentsStatus command: " . $e->getMessage());
            return 1;
        }
    }

    /**
     * Check accepted hourly appointments that should be moved to "On The Way".
     */
    private function checkAcceptedHourlyAppointments(Carbon $now): int
    {
        $currentHour = $now->copy()->startOfHour();
        $nextHour = $currentHour->copy()->addHour();

        $appointments = Appointment::with(['providerType.type', 'providerType.provider', 'user'])
            ->whereHas('providerType.type', function ($query) {
                $query->where('booking_type', 'hourly');
            })
            ->where('appointment_status', 2)
            ->whereDate('date', $now->toDateString())
            ->where('date', '>=', $currentHour)
            ->where('date', '<', $nextHour)
            ->get();

        if ($appointments->isEmpty()) {
            $this->info('No accepted hourly appointments need status check at this time.');
            return 0;
        }

        $this->info("Found {$appointments->count()} accepted hourly appointment(s) to check.");

        foreach ($appointments as $appointment) {
            $appointmentTime = Carbon::parse($appointment->date);
            $minutesSinceAppointment = $now->diffInMinutes($appointmentTime, false);

            if ($minutesSinceAppointment <= 0) {
                $minutesPassed = abs($minutesSinceAppointment);
                $sent = $this->sendAcceptedHourlyReminder($appointment, $minutesPassed);

                $this->info($sent
                    ? "Accepted hourly reminder sent for appointment #{$appointment->number} ({$minutesPassed} minute(s) after start)."
                    : "Accepted hourly reminder skipped for appointment #{$appointment->number}."
                );
            } else {
                $this->info(
                    "Appointment #{$appointment->number} hasn't started yet (starts in {$minutesSinceAppointment} minute(s))."
                );
            }
        }

        return $appointments->count();
    }

    /**
     * Check appointments stuck on "User arrived to provider" for one hour.
     */
    private function checkUserArrivedAppointments(Carbon $now): int
    {
        $cutoff = $now->copy()->subHour();
        $oldestAllowedUpdate = $now->copy()->subDay();

        $appointments = Appointment::with(['providerType.provider', 'user'])
            ->where('appointment_status', 7)
            ->whereBetween('updated_at', [$oldestAllowedUpdate, $cutoff])
            ->get();

        if ($appointments->isEmpty()) {
            $this->info('No "User arrived to provider" appointments need reminder at this time.');
            return 0;
        }

        $this->info("Found {$appointments->count()} 'User arrived to provider' appointment(s) to check.");

        foreach ($appointments as $appointment) {
            $minutesSinceLastUpdate = $appointment->updated_at?->diffInMinutes($now) ?? 0;

            $this->sendUserArrivedReminder($appointment, $minutesSinceLastUpdate);
        }

        return $appointments->count();
    }

    /**
     * Send reminder notification to provider for accepted hourly bookings.
     */
    private function sendAcceptedHourlyReminder(Appointment $appointment, int $minutesPassed): bool
    {
        try {
            $provider = $appointment->providerType?->provider;
            if (!$provider) {
                Log::warning("Accepted hourly reminder skipped: provider missing for appointment #{$appointment->id}");
                return false;
            }

            $userName = $appointment->user->name ?? 'Customer';

            $title = "⚠️ Appointment Time Started - Action Required";
            $body = "Appointment #{$appointment->number} with {$userName} started {$minutesPassed} minute(s) ago. Please update status to 'On The Way' immediately.";

            if ($this->providerReminderAlreadySent($provider->id, $title, $appointment->number)) {
                $this->info("Accepted hourly reminder already sent for appointment #{$appointment->number}; skipping duplicate.");
                return false;
            }

            Notification::create([
                'title' => $title,
                'body' => $body,
                'type' => 4,
                'provider_id' => $provider->id,
            ]);

            FCMController::sendMessageToProvider(
                $title,
                $body,
                $provider->id,
                [
                    'screen' => 'appointment',
                    'key' => 'appointment',
                    'appointment_id' => $appointment->id,
                    'appointment_status' => $appointment->appointment_status,
                ],
                'appointment'
            );

            Log::info("Hourly appointment reminder sent to provider ID: {$provider->id} for appointment #{$appointment->id}");
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send hourly appointment reminder: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send a one-time reminder if the provider left the appointment on
     * "User arrived to provider" for more than one hour.
     */
    private function sendUserArrivedReminder(Appointment $appointment, int $minutesSinceLastUpdate): void
    {
        try {
            $provider = $appointment->providerType?->provider;

            if (!$provider) {
                Log::warning("User arrived reminder skipped: provider missing for appointment #{$appointment->id}");
                return;
            }

            $userName = $appointment->user->name ?? 'Customer';
            $title = 'User Arrived Status Needs Update';
            $body = "Appointment #{$appointment->number} for {$userName} has been on 'User arrived to provider' for {$minutesSinceLastUpdate} minute(s). Please update the appointment status.";

            if ($this->providerReminderAlreadySent($provider->id, $title, $appointment->number)) {
                $this->info("User arrived reminder already sent for appointment #{$appointment->number}; skipping duplicate.");
                return;
            }

            Notification::create([
                'title' => $title,
                'body' => $body,
                'type' => 4,
                'provider_id' => $provider->id,
            ]);

            FCMController::sendMessageToProvider(
                $title,
                $body,
                $provider->id,
                [
                    'screen' => 'appointment',
                    'key' => 'appointment',
                    'appointment_id' => $appointment->id,
                    'appointment_status' => $appointment->appointment_status,
                ],
                'appointment'
            );

            Log::info("User arrived reminder sent to provider ID: {$provider->id} for appointment #{$appointment->id}");
        } catch (\Exception $e) {
            Log::error("Failed to send user arrived reminder: " . $e->getMessage());
        }
    }

    private function providerReminderAlreadySent(int $providerId, string $title, string $appointmentNumber): bool
    {
        return Notification::query()
            ->where('type', 4)
            ->where('provider_id', $providerId)
            ->where('title', $title)
            ->where('body', 'like', "%Appointment #{$appointmentNumber}%")
            ->exists();
    }
}
