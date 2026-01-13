<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SettlementCycle;
use App\Models\ProviderSettlement;
use App\Models\ProviderBan;
use App\Http\Controllers\Admin\FCMController;
use Carbon\Carbon;

class SendSettlementReminders extends Command
{
    protected $signature = 'settlement:send-reminders';
    protected $description = 'Send payment reminders and warnings for unpaid settlements';

    public function handle()
    {
        $this->info('Checking for unpaid settlements...');
        
        $today = now();
        $dayOfMonth = $today->day;

        // Day 15: First reminder for cycle 1-14
        if ($dayOfMonth == 15) {
            $this->sendReminder('first', 1);
        }

        // Day 17: Warning for cycle 1-14
        if ($dayOfMonth == 17) {
            $this->sendWarning(1);
        }

        // For second cycle (15-end), send reminders 1 and 3 days after end date
        $this->checkSecondCycleReminders($today);

        $this->info('✅ Reminder check completed!');
        return 0;
    }

    private function sendReminder($type, $daysSinceEnd)
    {
        $this->info("\n📧 Sending {$type} reminder...");

        // Get cycles that ended exactly $daysSinceEnd days ago
        $targetDate = now()->subDays($daysSinceEnd)->toDateString();
        
        $cycles = SettlementCycle::whereDate('end_date', $targetDate)
            ->where('status', 1) // Still active
            ->get();

        foreach ($cycles as $cycle) {
            $unpaidSettlements = ProviderSettlement::where('settlement_cycle_id', $cycle->id)
                ->where('payment_status', 1) // Pending
                ->with('provider')
                ->get();

            $this->info("Cycle: {$cycle->start_date->format('d M')} - {$cycle->end_date->format('d M')}");
            $this->info("Unpaid settlements: {$unpaidSettlements->count()}");

            foreach ($unpaidSettlements as $settlement) {
                $provider = $settlement->provider;
                
                if (!$provider) {
                    continue;
                }

                $title = $type == 'first' ? "Payment Reminder" : "Final Payment Warning";
                $body = "⚠️ Reminder: Your settlement payment is due!\n\n";
                $body .= "Period: {$cycle->start_date->format('d M')} - {$cycle->end_date->format('d M Y')}\n";
                $body .= "Amount due: {$settlement->commission_amount} JD\n\n";
                
                if ($type == 'first') {
                    $body .= "Please settle your payment within 2 days.";
                } else {
                    $body .= "⚠️ URGENT: Payment overdue! Please settle immediately to avoid account suspension.";
                }

                try {
                    // Save notification
                    \App\Models\Notification::create([
                        'title' => $title,
                        'body' => $body,
                        'type' => 2,
                        'provider_id' => $provider->id,
                    ]);

                    // Send FCM
                    FCMController::sendMessageToProvider($title, $body, $provider->id);
                    
                    $this->info("  ✅ Reminder sent to: {$provider->name_of_manager}");
                } catch (\Exception $e) {
                    $this->error("  ❌ Failed: " . $e->getMessage());
                }
            }
        }
    }

    private function sendWarning($daysSinceEnd)
    {
        $this->sendReminder('warning', $daysSinceEnd);
        
        // After day 17, ban providers with unpaid settlements
        $this->banUnpaidProviders();
    }

    private function banUnpaidProviders()
    {
        $this->info("\n🚫 Checking providers to ban for non-payment...");

        $targetDate = now()->subDays(3)->toDateString();
        
        $cycles = SettlementCycle::whereDate('end_date', '<', $targetDate)
            ->where('status', 1)
            ->get();

        foreach ($cycles as $cycle) {
            $unpaidSettlements = ProviderSettlement::where('settlement_cycle_id', $cycle->id)
                ->where('payment_status', 1) // Still unpaid
                ->with('provider')
                ->get();

            foreach ($unpaidSettlements as $settlement) {
                $provider = $settlement->provider;
                
                if (!$provider) {
                    continue;
                }

                // Check if provider already has an active ban for unpaid settlement
                $existingBan = ProviderBan::where('provider_id', $provider->id)
                    ->where('ban_reason', 'unpaid_settlement')
                    ->where('is_active', true)
                    ->first();

                if ($existingBan) {
                    $this->info("  ⏭️  Provider {$provider->name_of_manager} already banned");
                    continue;
                }

                // Create ban record
                $ban = ProviderBan::create([
                    'provider_id' => $provider->id,
                    'admin_id' => 1, // System admin
                    'ban_reason' => 'unpaid_settlement',
                    'ban_description' => "Provider failed to pay settlement dues for period: {$cycle->start_date->format('d M')} - {$cycle->end_date->format('d M Y')}. Amount due: {$settlement->commission_amount} JD",
                    'banned_at' => now(),
                    'ban_until' => null, // Indefinite until payment
                    'is_permanent' => false,
                    'is_active' => true,
                ]);

                // Send ban notification
                $title = "Account Suspended - Payment Overdue";
                $body = "❌ Your account has been suspended due to unpaid settlement.\n\n";
                $body .= "Period: {$cycle->start_date->format('d M')} - {$cycle->end_date->format('d M Y')}\n";
                $body .= "Amount due: {$settlement->commission_amount} JD\n\n";
                $body .= "💰 Payment Breakdown:\n";
                
                // Get payment type breakdown
                $cycleAppointments = \App\Models\AppointmentSettlement::where('settlement_cycle_id', $cycle->id)
                    ->where('provider_id', $provider->id)
                    ->get();
                
                $cashCommission = $cycleAppointments->where('payment_type', 'cash')->sum('commission_amount');
                $visaAmount = $cycleAppointments->where('payment_type', 'visa')->sum('provider_amount');
                $walletAmount = $cycleAppointments->where('payment_type', 'wallet')->sum('provider_amount');
                
                if ($cashCommission > 0) {
                    $body .= "• Cash commission to pay: {$cashCommission} JD\n";
                }
                if ($visaAmount > 0) {
                    $body .= "• Visa amount to receive: {$visaAmount} JD\n";
                }
                if ($walletAmount > 0) {
                    $body .= "• Wallet amount to receive: {$walletAmount} JD\n";
                }
                
                $body .= "\n⚠️ Your account will remain suspended until payment is received.";
                $body .= "\n📞 Please contact admin to settle your payment.";

                try {
                    // Save notification
                    \App\Models\Notification::create([
                        'title' => $title,
                        'body' => $body,
                        'type' => 2,
                        'provider_id' => $provider->id,
                    ]);

                    // Send FCM
                    FCMController::sendMessageToProvider($title, $body, $provider->id);
                    
                    $this->info("  🚫 Banned: {$provider->name_of_manager} (Amount: {$settlement->commission_amount} JD)");
                    
                    \Log::info("Provider banned for unpaid settlement", [
                        'provider_id' => $provider->id,
                        'ban_id' => $ban->id,
                        'cycle_id' => $cycle->id,
                        'amount_due' => $settlement->commission_amount,
                    ]);
                    
                } catch (\Exception $e) {
                    $this->error("  ❌ Failed to notify provider: " . $e->getMessage());
                    \Log::error("Failed to send ban notification", [
                        'provider_id' => $provider->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }
    }

    private function checkSecondCycleReminders($today)
    {
        // For cycles ending on last day of month
        $lastDayOfLastMonth = $today->copy()->subMonth()->endOfMonth();
        $daysSinceLastMonth = $today->diffInDays($lastDayOfLastMonth);

        if ($daysSinceLastMonth == 1) {
            $this->sendReminder('first', 1);
        } elseif ($daysSinceLastMonth == 3) {
            $this->sendWarning(3);
        }
    }
}