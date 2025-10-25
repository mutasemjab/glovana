<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\PointTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PointsReportController extends Controller
{
    /**
     * Display points report dashboard
     */
    public function index(Request $request)
    {
        $dateFrom = $request->get('date_from', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', Carbon::now()->format('Y-m-d'));
        $filterType = $request->get('filter_type', 'all'); // all, earned, deducted
        $topLimit = $request->get('top_limit', 10);
        $sortBy = $request->get('sort_by', 'total_points'); // total_points, earned_points, deducted_points, transactions_count

        // Build base query with date filters
        $baseQuery = PointTransaction::whereBetween('created_at', [
            Carbon::parse($dateFrom)->startOfDay(),
            Carbon::parse($dateTo)->endOfDay()
        ]);

        // Apply transaction type filter
        if ($filterType !== 'all') {
            $transactionType = $filterType === 'earned' ? 1 : 2;
            $baseQuery->where('type_of_transaction', $transactionType);
        }

        // Get top users by points earned in the period
        $topEarners = $this->getTopUsers($dateFrom, $dateTo, 'earned', $topLimit, $sortBy);
        
        // Get top users by points deducted in the period
        $topSpenders = $this->getTopUsers($dateFrom, $dateTo, 'deducted', $topLimit, $sortBy);

        // Get overall statistics
        $stats = $this->getOverallStats($dateFrom, $dateTo);

        // Get daily points activity for chart
        $dailyActivity = $this->getDailyActivity($dateFrom, $dateTo);

        // Get top performing admins/providers
        $topAdmins = $this->getTopPerformers('admin', $dateFrom, $dateTo, 5);
        $topProviders = $this->getTopPerformers('provider', $dateFrom, $dateTo, 5);

        // Get points distribution
        $pointsDistribution = $this->getPointsDistribution();

        return view('reports.points', compact(
            'topEarners',
            'topSpenders', 
            'stats',
            'dailyActivity',
            'topAdmins',
            'topProviders',
            'pointsDistribution',
            'dateFrom',
            'dateTo',
            'filterType',
            'topLimit',
            'sortBy'
        ));
    }

    /**
     * Get top users based on criteria
     */
    private function getTopUsers($dateFrom, $dateTo, $type, $limit, $sortBy)
    {
        $transactionType = $type === 'earned' ? 1 : 2;
        
        $query = User::select([
                'users.id',
                'users.name',
                'users.email',
                'users.photo',
                'users.total_points',
                'users.created_at as user_created_at'
            ])
            ->join('point_transactions', 'users.id', '=', 'point_transactions.user_id')
            ->whereBetween('point_transactions.created_at', [
                Carbon::parse($dateFrom)->startOfDay(),
                Carbon::parse($dateTo)->endOfDay()
            ])
            ->where('point_transactions.type_of_transaction', $transactionType)
            ->groupBy('users.id', 'users.name', 'users.email', 'users.photo', 'users.total_points', 'users.created_at')
            ->selectRaw('
                COUNT(point_transactions.id) as transactions_count,
                SUM(ABS(point_transactions.points)) as period_points,
                AVG(ABS(point_transactions.points)) as avg_transaction_amount
            ');

        // Add sorting
        switch ($sortBy) {
            case 'total_points':
                $query->orderBy('users.total_points', 'desc');
                break;
            case 'period_points':
                $query->orderBy('period_points', 'desc');
                break;
            case 'transactions_count':
                $query->orderBy('transactions_count', 'desc');
                break;
            case 'avg_transaction':
                $query->orderBy('avg_transaction_amount', 'desc');
                break;
            default:
                $query->orderBy('period_points', 'desc');
        }

        return $query->limit($limit)->get();
    }

    /**
     * Get overall statistics
     */
    private function getOverallStats($dateFrom, $dateTo)
    {
        $stats = [];

        // Period statistics
        $periodStats = PointTransaction::whereBetween('created_at', [
                Carbon::parse($dateFrom)->startOfDay(),
                Carbon::parse($dateTo)->endOfDay()
            ])
            ->selectRaw('
                SUM(CASE WHEN type_of_transaction = 1 THEN points ELSE 0 END) as total_earned,
                SUM(CASE WHEN type_of_transaction = 2 THEN ABS(points) ELSE 0 END) as total_deducted,
                COUNT(CASE WHEN type_of_transaction = 1 THEN 1 END) as earned_transactions,
                COUNT(CASE WHEN type_of_transaction = 2 THEN 1 END) as deducted_transactions,
                COUNT(*) as total_transactions,
                COUNT(DISTINCT user_id) as active_users
            ')
            ->first();

        // Overall platform statistics
        $overallStats = DB::table('users')
            ->selectRaw('
                SUM(total_points) as platform_total_points,
                COUNT(*) as total_users,
                AVG(total_points) as avg_user_points
            ')
            ->first();

        $allTimeTransactions = PointTransaction::selectRaw('
                SUM(CASE WHEN type_of_transaction = 1 THEN points ELSE 0 END) as all_time_earned,
                SUM(CASE WHEN type_of_transaction = 2 THEN ABS(points) ELSE 0 END) as all_time_deducted,
                COUNT(*) as all_time_transactions
            ')
            ->first();

        return [
            'period' => $periodStats,
            'overall' => $overallStats,
            'all_time' => $allTimeTransactions
        ];
    }

    /**
     * Get daily activity for chart
     */
    private function getDailyActivity($dateFrom, $dateTo)
    {
        return PointTransaction::whereBetween('created_at', [
                Carbon::parse($dateFrom)->startOfDay(),
                Carbon::parse($dateTo)->endOfDay()
            ])
            ->selectRaw('
                DATE(created_at) as date,
                SUM(CASE WHEN type_of_transaction = 1 THEN points ELSE 0 END) as earned,
                SUM(CASE WHEN type_of_transaction = 2 THEN ABS(points) ELSE 0 END) as deducted,
                COUNT(*) as transactions
            ')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    /**
     * Get top performing admins or providers
     */
    private function getTopPerformers($type, $dateFrom, $dateTo, $limit)
    {
        $column = $type === 'admin' ? 'admin_id' : 'provider_id';
        $relation = $type === 'admin' ? 'admin' : 'provider';

        return PointTransaction::with($relation)
            ->whereNotNull($column)
            ->whereBetween('created_at', [
                Carbon::parse($dateFrom)->startOfDay(),
                Carbon::parse($dateTo)->endOfDay()
            ])
            ->selectRaw("
                {$column},
                COUNT(*) as transactions_count,
                SUM(CASE WHEN type_of_transaction = 1 THEN points ELSE 0 END) as points_added,
                SUM(CASE WHEN type_of_transaction = 2 THEN ABS(points) ELSE 0 END) as points_deducted,
                SUM(ABS(points)) as total_points_handled
            ")
            ->groupBy($column)
            ->orderBy('total_points_handled', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get points distribution among users
     */
    private function getPointsDistribution()
    {
        return DB::table('users')
            ->selectRaw('
                CASE 
                    WHEN total_points = 0 THEN "0 Points"
                    WHEN total_points BETWEEN 1 AND 100 THEN "1-100 Points"
                    WHEN total_points BETWEEN 101 AND 500 THEN "101-500 Points"
                    WHEN total_points BETWEEN 501 AND 1000 THEN "501-1000 Points"
                    WHEN total_points BETWEEN 1001 AND 5000 THEN "1001-5000 Points"
                    ELSE "5000+ Points"
                END as range_label,
                COUNT(*) as user_count,
                SUM(total_points) as total_points_in_range
            ')
            ->groupBy('range_label')
            ->orderByRaw('MIN(total_points)')
            ->get();
    }

  

   

  
}