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
            ])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc');

            // Filter by transaction type if provided
            if ($request->has('type_of_transaction') && $request->type_of_transaction != '') {
                $transactions->where('type_of_transaction', $request->type_of_transaction);
            }

            $transactions = $transactions->paginate(10);

            // Add transaction type labels
            $transactions->getCollection()->transform(function ($transaction) {
                $transaction->transaction_type_label = $transaction->type_of_transaction == 1 ? 'Added' : 'Withdrawn';
                return $transaction;
            });

            return $this->success_response(
                'Points transactions retrieved successfully',
                [
                    'current_points' => $user->total_points,
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
            $minPointsToConvert = Setting::where('key', 'number_of_points_to_convert_to_money')->value('value');
            $onePointEqualMoney = Setting::where('key', 'one_point_equal_money')->value('value');

            if (!$minPointsToConvert || !$onePointEqualMoney) {
                return $this->error_response(
                    'Conversion settings not configured',
                    []
                );
            }

            // Check if user has enough points
            if ($user->total_points < $pointsToConvert) {
                return $this->error_response(
                    'Insufficient points balance',
                    [
                        'required_points' => $pointsToConvert,
                        'current_points' => $user->total_points
                    ]
                );
            }

            // Check minimum conversion requirement
            if ($pointsToConvert < $minPointsToConvert) {
                return $this->error_response(
                    "Minimum {$minPointsToConvert} points required for conversion",
                    [
                        'minimum_points' => $minPointsToConvert,
                        'provided_points' => $pointsToConvert
                    ]
                );
            }

            // Calculate money amount
            $moneyAmount = $pointsToConvert * $onePointEqualMoney;

            DB::beginTransaction();

            try {
                // Deduct points from user
                $user->decrement('total_points', $pointsToConvert);
                
                // Add money to user wallet
                $user->increment('balance', $moneyAmount);

                // Create points transaction record
                PointTransaction::create([
                    'user_id' => $user->id,
                    'points' => $pointsToConvert,
                    'type_of_transaction' => 2, // withdrawal
                    'note' => "Converted {$pointsToConvert} points to {$moneyAmount} JD"
                ]);

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

                return $this->success_response(
                    'Points converted to money successfully',
                    [
                        'converted_points' => $pointsToConvert,
                        'money_received' => $moneyAmount,
                        'current_points' => $user->total_points,
                        'current_balance' => $user->balance
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

}