<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SettlementCycle;
use App\Models\Provider;
use App\Http\Controllers\Admin\FCMController;
use Carbon\Carbon;

class CheckSettlementPayments extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'settlement:check-payments';

    /**
     * The console command description.
     */
    protected $description = 'Check settlement cycles and send notifications for pending payments';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting settlement payment check...');
        
        $today = now();
        $this->info("Today's date: " . $today->format('Y-m-d'));

        // Check cycles ending today (Day 14 or last day of month)
        $cyclesEndingToday = SettlementCycle::where('status', 1) // Active cycles
            ->whereDate('end_date', $today->toDateString())
            ->with(['appointmentSettlements.provider'])
            ->get();

        if ($cyclesEndingToday->isEmpty()) {
            $this->info('No settlement cycles ending today.');
            return 0;
        }

        $this->info("Found {$cyclesEndingToday->count()} cycle(s) ending today.");

        foreach ($cyclesEndingToday as $cycle) {
            $this->info("\nProcessing cycle: {$cycle->start_date->format('d M')} - {$cycle->end_date->format('d M Y')}");
            
            // Get all providers in this cycle
            $providerIds = $cycle->appointmentSettlements->pluck('provider_id')->unique();
            
            $this->info("Total providers: {$providerIds->count()}");

            foreach ($providerIds as $providerId) {
                $provider = Provider::find($providerId);
                
                if (!$provider) {
                    continue;
                }

                // Calculate provider's settlement for this cycle
                $providerAppointments = $cycle->appointmentSettlements()
                    ->where('provider_id', $providerId)
                    ->get();

                $totalAmount = $providerAppointments->sum('appointment_amount');
                $totalCommission = $providerAppointments->sum('commission_amount');
                $netAmount = $providerAppointments->sum('provider_amount');
                $totalAppointments = $providerAppointments->count();

                // Breakdown by payment type
                $cashAppointments = $providerAppointments->where('payment_type', 'cash');
                $visaAppointments = $providerAppointments->where('payment_type', 'visa');
                $walletAppointments = $providerAppointments->where('payment_type', 'wallet');

                $cashCommission = $cashAppointments->sum('commission_amount');
                $visaAmount = $visaAppointments->sum('provider_amount');
                $walletAmount = $walletAppointments->sum('provider_amount');

                // Create or update provider settlement
                $providerSettlement = \App\Models\ProviderSettlement::updateOrCreate(
                    [
                        'settlement_cycle_id' => $cycle->id,
                        'provider_id' => $providerId,
                    ],
                    [
                        'total_appointments_amount' => $totalAmount,
                        'commission_amount' => $totalCommission,
                        'net_amount' => $netAmount,
                        'total_appointments' => $totalAppointments,
                        'payment_status' => 1, // Pending
                    ]
                );

                $this->info("\nProvider: {$provider->name_of_manager} (ID: {$providerId})");
                $this->info("  Total appointments: {$totalAppointments}");
                $this->info("  Total amount: {$totalAmount} JD");
                $this->info("  Commission: {$totalCommission} JD");
                $this->info("  Net amount: {$netAmount} JD");

                // Send notification to provider
                $this->sendSettlementNotification($provider, $cycle, [
                    'total_appointments' => $totalAppointments,
                    'total_amount' => $totalAmount,
                    'commission' => $totalCommission,
                    'net_amount' => $netAmount,
                    'cash_commission' => $cashCommission,
                    'visa_amount' => $visaAmount,
                    'wallet_amount' => $walletAmount,
                ]);
            }
        }

        $this->info("\n✅ Settlement payment check completed successfully!");
        return 0;
    }

    /**
     * Send settlement notification to provider
     */
    private function sendSettlementNotification($provider, $cycle, $data)
    {
        try {
            $period = $cycle->start_date->format('d M') . ' - ' . $cycle->end_date->format('d M Y');
            
            $title = "Settlement Period Ended - Payment Due";
            $body = "Settlement period ({$period}) has ended.\n\n";
            $body .= "📊 Summary:\n";
            $body .= "• Total Appointments: {$data['total_appointments']}\n";
            $body .= "• Total Amount: {$data['total_amount']} JD\n";
            $body .= "• Commission: {$data['commission']} JD\n";
            $body .= "• Net Amount: {$data['net_amount']} JD\n\n";
            $body .= "💰 Payment Breakdown:\n";
            $body .= "• Cash (Commission to pay): {$data['cash_commission']} JD\n";
            $body .= "• Visa (Amount to receive): {$data['visa_amount']} JD\n";
            $body .= "• Wallet (Amount to receive): {$data['wallet_amount']} JD\n\n";
            $body .= "⏰ Payment deadline: Within 3 days";

            // Save notification to database
            \App\Models\Notification::create([
                'title' => $title,
                'body' => $body,
                'type' => 2, // Provider type
                'provider_id' => $provider->id,
            ]);

            // Send FCM notification
            FCMController::sendMessageToProvider($title, $body, $provider->id);

            $this->info("  ✅ Notification sent to provider");
            
            \Log::info("Settlement notification sent", [
                'provider_id' => $provider->id,
                'cycle_id' => $cycle->id,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            $this->error("  ❌ Failed to send notification: " . $e->getMessage());
            \Log::error("Failed to send settlement notification", [
                'provider_id' => $provider->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}