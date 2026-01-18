<?php

namespace App\Services;

use App\Models\User;
use App\Models\Order;
use App\Models\Appointment;
use App\Models\PointTransaction;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PointsService
{
    private function getSetting($key, $default = 0)
    {
        $setting = Setting::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    private function calculateExpiryDate()
    {
        $expiryMonths = $this->getSetting('points_expiry_months', 12);
        return now()->addMonths($expiryMonths);
    }

    public function awardFirstOrderPoints(User $user, Order $order)
    {
        $previousOrders = Order::where('user_id', $user->id)
            ->where('order_status', 4)
            ->where('id', '!=', $order->id)
            ->count();

        if ($previousOrders > 0) {
            return null;
        }

        $bonusPoints = $this->getSetting('first_order_bonus_points', 50);

        if ($bonusPoints <= 0) {
            return null;
        }

        return $this->addPoints(
            $user,
            $bonusPoints,
            'first_order',
            "First order bonus - Order #{$order->number}",
            $order->id
        );
    }

    public function awardOrderPurchasePoints(User $user, Order $order)
    {
        if ($order->payment_type !== 'cash') {
            return null;
        }

        $pointsPerDinar = $this->getSetting('points_per_dinar', 1);
        $eligibleAmount = $order->total_prices - ($order->delivery_fee ?? 0);
        
        if ($eligibleAmount <= 0) {
            return null;
        }

        $points = floor($eligibleAmount * $pointsPerDinar);

        if ($points <= 0) {
            return null;
        }

        return $this->addPoints(
            $user,
            $points,
            'order_purchase',
            "Points earned from order #{$order->number} (Cash payment: {$eligibleAmount} JD)",
            $order->id
        );
    }

    public function awardRatingPoints(User $user, Appointment $appointment)
    {
        if ($appointment->rating_points_awarded == 1) {
            return null;
        }

        $ratingPoints = $this->getSetting('service_rating_points', 10);

        if ($ratingPoints <= 0) {
            return null;
        }

        $transaction = $this->addPoints(
            $user,
            $ratingPoints,
            'rating',
            "Service rating points - Appointment #{$appointment->number}",
            null,
            $appointment->id
        );

        if ($transaction) {
            $appointment->rating_points_awarded = 1;
            $appointment->save();
        }

        return $transaction;
    }

    public function awardSalonBookingPoints(User $user, Appointment $appointment)
    {
        if ($appointment->payment_type !== 'cash') {
            return null;
        }

        $pointsPercentage = $this->getSetting('salon_booking_points_percentage', 10);
        $points = floor(($appointment->total_prices * $pointsPercentage) / 100);

        if ($points <= 0) {
            return null;
        }

        $transaction = $this->addPoints(
            $user,
            $points,
            'salon_booking',
            "Salon booking points ({$pointsPercentage}%) - Appointment #{$appointment->number}",
            null,
            $appointment->id
        );

        if ($appointment->providerType && $appointment->providerType->is_vip == 1) {
            $this->awardVipSalonBonus($user, $appointment);
        }

        return $transaction;
    }

    private function awardVipSalonBonus(User $user, Appointment $appointment)
    {
        $vipBonusPoints = $this->getSetting('vip_salon_extra_points', 20);

        if ($vipBonusPoints <= 0) {
            return null;
        }

        return $this->addPoints(
            $user,
            $vipBonusPoints,
            'vip_bonus',
            "VIP salon bonus - Appointment #{$appointment->number}",
            null,
            $appointment->id
        );
    }

    public function addPoints(User $user, int $points, string $source, string $note, $orderId = null, $appointmentId = null)
    {
        if ($points <= 0) {
            return null;
        }

        DB::beginTransaction();
        try {
            $transaction = PointTransaction::create([
                'user_id' => $user->id,
                'points' => $points,
                'type_of_transaction' => 1,
                'note' => $note,
                'expires_at' => $this->calculateExpiryDate(),
                'status' => 1,
                'source' => $source,
                'order_id' => $orderId,
                'appointment_id' => $appointmentId
            ]);

            $user->increment('total_points', $points);

            DB::commit();

            Log::info("Points added to user", [
                'user_id' => $user->id,
                'points' => $points,
                'source' => $source,
                'transaction_id' => $transaction->id
            ]);

            return $transaction;
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Failed to add points: " . $e->getMessage());
            throw $e;
        }
    }

    public function calculateMaxRedeemablePoints(User $user, float $orderAmount)
    {
        $maxPercentage = $this->getSetting('max_points_redemption_percentage', 50);
        $maxCoverageAmount = ($orderAmount * $maxPercentage) / 100;
        
        $pointsToConvert = $this->getSetting('number_of_points_to_convert_to_money', 100);
        $moneyPerPoint = $this->getSetting('points_per_dinar', 1);
        
        $maxPointsByAmount = floor(($maxCoverageAmount / $moneyPerPoint) * $pointsToConvert);
        $availablePoints = $this->getAvailablePoints($user);
        
        return min($maxPointsByAmount, $availablePoints);
    }

    public function redeemPoints(User $user, int $pointsToRedeem, float $orderAmount, string $orderType, $orderId = null, $appointmentId = null)
    {
        $maxRedeemable = $this->calculateMaxRedeemablePoints($user, $orderAmount);
        
        if ($pointsToRedeem > $maxRedeemable) {
            throw new \Exception("Cannot redeem more than {$maxRedeemable} points for this order");
        }

        $pointsToConvert = $this->getSetting('number_of_points_to_convert_to_money', 100);
        $moneyPerPoint = $this->getSetting('points_per_dinar', 1);
        
        $discountAmount = ($pointsToRedeem * $moneyPerPoint) / $pointsToConvert;

        DB::beginTransaction();
        try {
            $this->deductPointsFIFO($user, $pointsToRedeem, $orderType, $orderId, $appointmentId);
            $user->decrement('total_points', $pointsToRedeem);

            DB::commit();

            Log::info("Points redeemed", [
                'user_id' => $user->id,
                'points' => $pointsToRedeem,
                'discount_amount' => $discountAmount,
                'order_type' => $orderType
            ]);

            return [
                'points_redeemed' => $pointsToRedeem,
                'discount_amount' => $discountAmount
            ];
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Failed to redeem points: " . $e->getMessage());
            throw $e;
        }
    }

    private function deductPointsFIFO(User $user, int $pointsToDeduct, string $orderType, $orderId = null, $appointmentId = null)
    {
        $remainingPoints = $pointsToDeduct;

        $transactions = PointTransaction::where('user_id', $user->id)
            ->where('type_of_transaction', 1)
            ->where('status', 1)
            ->notExpired()
            ->orderBy('created_at', 'asc')
            ->get();

        foreach ($transactions as $transaction) {
            if ($remainingPoints <= 0) {
                break;
            }

            if ($transaction->points >= $remainingPoints) {
                $deductedPoints = $remainingPoints;
                $remainingPoints = 0;
            } else {
                $deductedPoints = $transaction->points;
                $remainingPoints -= $deductedPoints;
            }

            PointTransaction::create([
                'user_id' => $user->id,
                'points' => $deductedPoints,
                'type_of_transaction' => 2,
                'note' => "Points redeemed for {$orderType}",
                'status' => 3,
                'source' => 'redemption',
                'order_id' => $orderId,
                'appointment_id' => $appointmentId
            ]);

            $transaction->points -= $deductedPoints;
            if ($transaction->points <= 0) {
                $transaction->status = 3;
            }
            $transaction->save();
        }

        if ($remainingPoints > 0) {
            throw new \Exception("Insufficient available points");
        }
    }

    public function getAvailablePoints(User $user)
    {
        return PointTransaction::where('user_id', $user->id)
            ->where('type_of_transaction', 1)
            ->where('status', 1)
            ->notExpired()
            ->sum('points');
    }

    public function getUserPointsBreakdown(User $user)
    {
        $totalEarned = PointTransaction::where('user_id', $user->id)
            ->where('type_of_transaction', 1)
            ->sum('points');

        $totalRedeemed = PointTransaction::where('user_id', $user->id)
            ->where('type_of_transaction', 2)
            ->sum('points');

        $expired = PointTransaction::where('user_id', $user->id)
            ->where('type_of_transaction', 1)
            ->where('status', 2)
            ->sum('points');

        $available = $this->getAvailablePoints($user);

        $expiringSoon = PointTransaction::where('user_id', $user->id)
            ->where('type_of_transaction', 1)
            ->where('status', 1)
            ->where('expires_at', '<=', now()->addDays(30))
            ->where('expires_at', '>', now())
            ->sum('points');

        return [
            'total_points' => $user->total_points,
            'available_points' => $available,
            'total_earned' => $totalEarned,
            'total_redeemed' => $totalRedeemed,
            'expired_points' => $expired,
            'expiring_soon' => $expiringSoon
        ];
    }

    public function expireOldPoints()
    {
        DB::beginTransaction();
        try {
            $expiredTransactions = PointTransaction::where('type_of_transaction', 1)
                ->where('status', 1)
                ->where('expires_at', '<=', now())
                ->get();

            foreach ($expiredTransactions as $transaction) {
                $transaction->status = 2;
                $transaction->save();

                $user = $transaction->user;
                if ($user) {
                    $user->decrement('total_points', $transaction->points);
                }

                Log::info("Points expired", [
                    'transaction_id' => $transaction->id,
                    'user_id' => $transaction->user_id,
                    'points' => $transaction->points
                ]);
            }

            DB::commit();

            return $expiredTransactions->count();
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Failed to expire points: " . $e->getMessage());
            throw $e;
        }
    }

    public function getPointsHistory(User $user, $perPage = 15)
    {
        return PointTransaction::where('user_id', $user->id)
            ->with(['order', 'appointment'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }
}