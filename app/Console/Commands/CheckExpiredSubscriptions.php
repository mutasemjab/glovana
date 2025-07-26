<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\VipSubscription;
use Carbon\Carbon;

class CheckExpiredSubscriptions extends Command
{
    protected $signature = 'subscriptions:check-expired';
    protected $description = 'Check and update expired VIP subscriptions';

    public function handle()
    {
        $expiredCount = 0;
        
        $expiredSubscriptions = VipSubscription::where('end_date', '<', Carbon::now()->toDateString())
                                              ->where('status', '!=', 3)
                                              ->get();

        foreach ($expiredSubscriptions as $subscription) {
            $subscription->update(['status' => 3]);
            $subscription->providerType->update(['is_vip' => 2]);
            $expiredCount++;
        }

        $this->info("Updated {$expiredCount} expired subscriptions.");
        
        return 0;
    }
}
