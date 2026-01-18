<?php
namespace App\Http\Controllers\Api\v1\User;
use App\Http\Controllers\Controller;
use App\Models\PointsTransaction;
use App\Models\PointTransaction;
use App\Models\WalletTransaction;
use App\Models\User;
use App\Models\Provider;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Traits\Responses;

class PointsController extends Controller
{
    use Responses;

    protected $pointsService;

    public function __construct(PointsService $pointsService)
    {
        $this->pointsService = $pointsService;
    }

    /**
     * Get points transactions history
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            
            $transactions = PointTransaction::with([
                'user:id,name,phone',
                'provider:id,name_of_manager,phone',
                'order:id,number',
                'appointment:id,number'
            ])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc');

            // Filter by transaction type if provided
            if ($request->has('type_of_transaction') && $request->type_of_transaction != '') {
                $transactions->where('type_of_transaction', $request->type_of_transaction);
            }

            // Filter by status (active, expired, used)
            if ($request->has('status') && $request->status != '') {
                $transactions->where('status', $request->status);
            }

            // Filter by source
            if ($request->has('source') && $request->source != '') {
                $transactions->where('source', $request->source);
            }

            $transactions = $transactions->paginate(10);

            // Add transaction labels
            $transactions->getCollection()->transform(function ($transaction) {
                $transaction->transaction_type_label = $transaction->type_of_transaction == 1 ? 'Added' : 'Withdrawn';
                $transaction->status_label = $this->getStatusLabel($transaction->status);
                $transaction->source_label = $this->getSourceLabel($transaction->source);
                $transaction->is_expired = $transaction->expires_at && $transaction->expires_at < now();
                return $transaction;
            });

            // ✅ Get user points breakdown using PointsService
            $pointsBreakdown = $this->pointsService->getUserPointsBreakdown($user);

            return $this->success_response(
                'Points transactions retrieved successfully',
                [
                    'points_summary' => $pointsBreakdown,
                    'transactions' => $transactions
                ]
            );

        } catch (\Exception $e) {
            return $this->error_response(
                'Failed to retrieve points transactions',
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Convert points to money
     */
    public function convertPointsToMoney(Request $request)
    {
        try {
            $user = Auth::user();

            // Validation
            $validator = Validator::make($request->all(), [
                'points_to_convert' => 'required|integer|min:1'
            ]);

            if ($validator->fails()) {
                return $this->error_response(
                    'Validation failed',
                    $validator->errors()
                );
            }

            $pointsToConvert = $request->points_to_convert;

            // Get conversion settings
            $minPointsToConvert = Setting::where('key', 'number_of_points_to_convert_to_money')->value('value') ?? 100;
            $onePointEqualMoney = Setting::where('key', 'points_per_dinar')->value('value') ?? 1;

            if (!$minPointsToConvert || !$onePointEqualMoney) {
                return $this->error_response(
                    'Conversion settings not configured',
                    []
                );
            }

            // ✅ Check minimum conversion requirement first
            if ($pointsToConvert < $minPointsToConvert) {
                return $this->error_response(
                    "Minimum {$minPointsToConvert} points required for conversion",
                    [
                        'minimum_points' => (int)$minPointsToConvert,
                        'provided_points' => $pointsToConvert,
                        'shortage' => $minPointsToConvert - $pointsToConvert
                    ]
                );
            }

            // ✅ Get AVAILABLE points (not expired, not used) - الطريقة الصحيحة
            $availablePoints = $this->pointsService->getAvailablePoints($user);

            // ✅ Check if user has enough AVAILABLE points
            if ($availablePoints < $pointsToConvert) {
                return $this->error_response(
                    'Insufficient available points',
                    [
                        'required_points' => $pointsToConvert,
                        'available_points' => $availablePoints,
                        'total_points' => $user->total_points,
                        'shortage' => $pointsToConvert - $availablePoints,
                        'note' => 'Some of your points may have expired or been used'
                    ]
                );
            }

            // Calculate money amount
            $moneyAmount = ($pointsToConvert * $onePointEqualMoney) / $minPointsToConvert;

            DB::beginTransaction();

            try {
                // ✅ Deduct points using FIFO method (oldest points first)
                $this->deductPointsForConversion($user, $pointsToConvert);

                // Deduct from total_points
                $user->decrement('total_points', $pointsToConvert);
                
                // Add money to user wallet
                $user->increment('balance', $moneyAmount);

                // Create wallet transaction record
                WalletTransaction::create([
                    'user_id' => $user->id,
                    'amount' => $moneyAmount,
                    'type_of_transaction' => 1, // add
                    'note' => "Converted from {$pointsToConvert} points"
                ]);

                DB::commit();

                // Refresh user data
                $user->refresh();

                // Get updated available points
                $updatedAvailablePoints = $this->pointsService->getAvailablePoints($user);

                return $this->success_response(
                    'Points converted to money successfully',
                    [
                        'converted_points' => $pointsToConvert,
                        'money_received' => $moneyAmount,
                        'conversion_rate' => "{$minPointsToConvert} points = {$onePointEqualMoney} JD",
                        'remaining_points' => [
                            'total_points' => $user->total_points,
                            'available_points' => $updatedAvailablePoints,
                            'current_balance' => $user->balance
                        ]
                    ]
                );

            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }

        } catch (\Exception $e) {
            return $this->error_response(
                'Failed to convert points to money',
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * ✅ Deduct points using FIFO method for conversion
     */
    private function deductPointsForConversion($user, $pointsToDeduct)
    {
        $remainingPoints = $pointsToDeduct;

        // Get active, non-expired points ordered by creation date (FIFO)
        $transactions = PointTransaction::where('user_id', $user->id)
            ->where('type_of_transaction', 1) // Only earned points
            ->where('status', 1) // Active
            ->notExpired() // Using the scope from PointTransaction model
            ->orderBy('created_at', 'asc') // Oldest first (FIFO)
            ->get();

        foreach ($transactions as $transaction) {
            if ($remainingPoints <= 0) {
                break;
            }

            if ($transaction->points >= $remainingPoints) {
                // This transaction has enough points
                $deductedPoints = $remainingPoints;
                $remainingPoints = 0;
            } else {
                // Use all points from this transaction
                $deductedPoints = $transaction->points;
                $remainingPoints -= $deductedPoints;
            }

            // Create deduction transaction record
            PointTransaction::create([
                'user_id' => $user->id,
                'points' => $deductedPoints,
                'type_of_transaction' => 2, // withdrawal
                'note' => "Converted {$deductedPoints} points to money",
                'status' => 3, // Used
                'source' => 'conversion',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Update or mark original transaction as used
            $transaction->points -= $deductedPoints;
            if ($transaction->points <= 0) {
                $transaction->status = 3; // Mark as fully used
            }
            $transaction->save();

            \Log::info("Points deducted for conversion", [
                'user_id' => $user->id,
                'transaction_id' => $transaction->id,
                'deducted_points' => $deductedPoints,
                'remaining_in_transaction' => $transaction->points
            ]);
        }

        if ($remainingPoints > 0) {
            throw new \Exception("Insufficient available points for conversion");
        }
    }



    /**
     * Helper: Get status label
     */
    private function getStatusLabel($status)
    {
        $labels = [
            1 => 'Active',
            2 => 'Expired',
            3 => 'Used'
        ];
        return $labels[$status] ?? 'Unknown';
    }

    /**
     * Helper: Get source label
     */
    private function getSourceLabel($source)
    {
        $labels = [
            'first_order' => 'First Order Bonus',
            'order_purchase' => 'Order Purchase',
            'rating' => 'Service Rating',
            'salon_booking' => 'Salon Booking',
            'vip_bonus' => 'VIP Bonus',
            'conversion' => 'Converted to Money',
            'redemption' => 'Used for Discount',
            'admin_adjustment' => 'Admin Adjustment'
        ];
        return $labels[$source] ?? $source;
    }
}