<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\PointTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PointsController extends Controller
{
    /**
     * Update user points
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'points' => 'required|integer|min:1',
            'type_of_transaction' => 'required|in:1,2', // 1 = add, 2 = deduct
            'note' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $user = User::findOrFail($request->user_id);
            $points = $request->points;
            $type = $request->type_of_transaction;
            $note = $request->note;

            // Calculate new points total
            if ($type == 1) { // Add points
                $newTotal = $user->total_points + $points;
                $transactionPoints = $points;
            } else { // Deduct points
                $newTotal = $user->total_points - $points;
                $transactionPoints = -$points;
            }

            // Update user's total points
            $user->update(['total_points' => $newTotal]);

            // Create point transaction record
            PointTransaction::create([
                'user_id' => $user->id,
                'admin_id' => Auth::guard('admin')->id(),
                'points' => $transactionPoints,
                'type_of_transaction' => $type,
                'note' => $note
            ]);

            DB::commit();

            $message = $type == 1 ? 
                "Successfully added {$points} points to {$user->name}. New total: {$newTotal} points." :
                "Successfully deducted {$points} points from {$user->name}. New total: {$newTotal} points.";

            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Failed to update points. Please try again.');
        }
    }

    /**
     * Show points transaction history for a user
     */
    public function history(User $user, Request $request)
    {
        $query = PointTransaction::where('user_id', $user->id)
            ->with(['admin:id,name', 'provider'])
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('type')) {
            $query->where('type_of_transaction', $request->type);
        }

        $transactions = $query->paginate(20);

        // Calculate statistics
        $totalEarned = PointTransaction::where('user_id', $user->id)
            ->where('type_of_transaction', 1)
            ->sum('points');

        $totalDeducted = abs(PointTransaction::where('user_id', $user->id)
            ->where('type_of_transaction', 2)
            ->sum('points'));

        return view('admin.users.points_history', compact(
            'user', 
            'transactions', 
            'totalEarned', 
            'totalDeducted'
        ));
    }
}