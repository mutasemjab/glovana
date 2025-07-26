<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\NoteVoucher;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Provider;
use App\Models\Setting;
use App\Models\Shop;
use App\Models\WalletTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ProviderReportController extends Controller
{

    public function index()
    {
        $providers = Provider::with(['providerTypes', 'walletTransactions'])
            ->orderBy('name_of_manager')
            ->paginate(20);
        
        // Add completed orders count to each provider
        $providers->transform(function ($provider) {
            $provider->completed_orders_last_30_days = $this->getCompletedOrdersLast30Days($provider->id);
            return $provider;
        });
            
        return view('reports.providers.index', compact('providers'));
    }
    
    /**
     * Show detailed report for a single provider
     */
    public function show($id)
    {
        $provider = Provider::with(['providerTypes', 'walletTransactions'])->findOrFail($id);
        
        $reportData = [
            'completed_orders_last_30_days' => $this->getCompletedOrdersLast30Days($id),
            'monthly_earnings' => $this->getMonthlyEarnings($id),
            'glovana_commission' => $this->getGlovanaCommission($id),
            'financial_transactions' => $this->getFinancialTransactions($id),
            'unpaid_commission' => $this->getUnpaidCommission($id),
            'payment_reminder' => $this->checkPaymentReminder($id),
        ];
        
        return view('reports.providers.show', compact('provider', 'reportData'));
    }

    /**
     * Get provider report with all required metrics
     */
    public function getProviderReport(Request $request, $providerId)
    {
        // Validate provider exists
        $provider = Provider::findOrFail($providerId);
        
        // Get data for the report
        $completedOrdersLast30Days = $this->getCompletedOrdersLast30Days($providerId);
        $monthlyEarnings = $this->getMonthlyEarnings($providerId);
        $glovanaCommission = $this->getGlovanaCommission($providerId);
        $financialTransactions = $this->getFinancialTransactions($providerId);
        $unpaidCommission = $this->getUnpaidCommission($providerId);
        
        // Check if payment reminder is needed
        $paymentReminder = $this->checkPaymentReminder($providerId);
        
        return response()->json([
            'success' => true,
            'data' => [
                'provider' => $provider,
                'completed_orders_last_30_days' => $completedOrdersLast30Days,
                'monthly_earnings' => $monthlyEarnings,
                'glovana_commission' => $glovanaCommission,
                'financial_transactions' => $financialTransactions,
                'unpaid_commission' => $unpaidCommission,
                'payment_reminder' => $paymentReminder,
            ]
        ]);
    }
    
    /**
     * Get completed orders count for last 30 days
     */
    protected function getCompletedOrdersLast30Days($providerId)
    {
        $date = Carbon::now()->subDays(30);
        
        return Appointment::whereHas('providerType', function($query) use ($providerId) {
                $query->where('provider_id', $providerId);
            })
            ->where('appointment_status', 4) // Delivered status
            ->where('created_at', '>=', $date)
            ->count();
    }
    
    /**
     * Get monthly earnings (current month)
     */
    protected function getMonthlyEarnings($providerId)
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        
        $appointments = Appointment::whereHas('providerType', function($query) use ($providerId) {
                $query->where('provider_id', $providerId);
            })
            ->where('appointment_status', 4) // Delivered status
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->get();
            
        return [
            'total_earnings' => $appointments->sum('total_prices'),
            'total_after_commission' => $appointments->sum(function($appointment) {
                return $appointment->total_prices - $this->calculateCommission($appointment->total_prices);
            }),
            'currency' => 'JOD' // Assuming Jordanian Dinar based on +962 country code
        ];
    }
    
    /**
     * Get Glovana commission for the provider
     */
    protected function getGlovanaCommission($providerId)
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        
        $appointments = Appointment::whereHas('providerType', function($query) use ($providerId) {
                $query->where('provider_id', $providerId);
            })
            ->where('appointment_status', 4) // Delivered status
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->get();
            
        $totalCommission = $appointments->sum(function($appointment) {
            return $this->calculateCommission($appointment->total_prices);
        });
        
        $paidCommission = WalletTransaction::where('provider_id', $providerId)
            ->where('type_of_transaction', 2) // Withdrawal (assuming this is Glovana taking commission)
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->sum('amount');
            
        return [
            'total_commission' => $totalCommission,
            'paid_commission' => $paidCommission,
            'unpaid_commission' => $totalCommission - $paidCommission,
            'currency' => 'JOD'
        ];
    }
    
    /**
     * Get financial transactions history
     */
    protected function getFinancialTransactions($providerId)
    {
        return WalletTransaction::where('provider_id', $providerId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($transaction) {
                return [
                    'date' => $transaction->created_at->format('Y-m-d H:i:s'),
                    'amount' => $transaction->amount,
                    'type' => $transaction->type_of_transaction == 1 ? 'Deposit' : 'Withdrawal',
                    'note' => $transaction->note,
                ];
            });
    }
    
    /**
     * Get unpaid commission that Glovana received from customers
     */
    protected function getUnpaidCommission($providerId)
    {
        $appointments = Appointment::whereHas('providerType', function($query) use ($providerId) {
                $query->where('provider_id', $providerId);
            })
            ->where('appointment_status', 4) // Delivered status
            ->where('payment_type', '!=', 'cash')
            ->where('payment_status', 1) // Paid by customer
            ->get();
            
        $totalCommission = $appointments->sum(function($appointment) {
            return $this->calculateCommission($appointment->total_prices);
        });
        
        $paidCommission = WalletTransaction::where('provider_id', $providerId)
            ->where('type_of_transaction', 2) // Withdrawal
            ->sum('amount');
            
        return $totalCommission - $paidCommission;
    }
    
    /**
     * Check if payment reminder is needed (after 14 days)
     */
    protected function checkPaymentReminder($providerId)
    {
        $unpaidCommission = $this->getUnpaidCommission($providerId);
        
        if ($unpaidCommission <= 0) {
            return null;
        }
        
        // Get the oldest unpaid appointment
        $oldestUnpaid = Appointment::whereHas('providerType', function($query) use ($providerId) {
                $query->where('provider_id', $providerId);
            })
            ->where('appointment_status', 4) // Delivered status
            ->where('payment_type', '!=', 'cash')
            ->where('payment_status', 1) // Paid by customer
            ->orderBy('created_at', 'asc')
            ->first();
            
        if ($oldestUnpaid && $oldestUnpaid->created_at->diffInDays(Carbon::now()) >= 14) {
            return [
                'message' => 'You have unpaid commission that is overdue for more than 14 days',
                'amount_due' => $unpaidCommission,
                'currency' => 'JOD',
                'oldest_unpaid_date' => $oldestUnpaid->created_at->format('Y-m-d')
            ];
        }
        
        return null;
    }
    
    /**
     * Calculate Glovana commission (adjust this based on your business logic)
     */
    protected function calculateCommission($totalAmount)
    {
        $setting = Setting::where('key', 'commission_of_admin')->first();
        return $setting ? $setting->value : 1.5; // Default 1.5%
    }
    

}
