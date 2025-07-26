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
use Carbon\Carbon;
use Illuminate\Http\Request;

class PaymentReportController extends Controller
{
    public function paymentReport(Request $request)
    {
        $period = $request->get('period', 'daily'); // daily, monthly, yearly
        $providerId = $request->get('provider_id');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        
        // Set default date range based on period
        if (!$dateFrom || !$dateTo) {
            switch ($period) {
                case 'yearly':
                    $dateFrom = Carbon::now()->startOfYear()->format('Y-m-d');
                    $dateTo = Carbon::now()->endOfYear()->format('Y-m-d');
                    break;
                case 'monthly':
                    $dateFrom = Carbon::now()->startOfMonth()->format('Y-m-d');
                    $dateTo = Carbon::now()->endOfMonth()->format('Y-m-d');
                    break;
                default: // daily
                    $dateFrom = Carbon::now()->format('Y-m-d');
                    $dateTo = Carbon::now()->format('Y-m-d');
                    break;
            }
        }

        $providers = $this->getProvidersReport($providerId, $dateFrom, $dateTo, $period);
        $summary = $this->calculateSummary($providers);
        
        return view('reports.payment', compact(
            'providers', 
            'summary', 
            'request', 
            'period',
            'dateFrom',
            'dateTo'
        ));
    }

    private function getProvidersReport($providerId, $dateFrom, $dateTo, $period)
    {
        $query = Provider::with(['providerTypes.appointments' => function($q) use ($dateFrom, $dateTo) {
            $q->whereBetween('date', [$dateFrom, $dateTo])
              ->where('payment_status', 1); // Only paid appointments
        }, 'walletTransactions' => function($q) use ($dateFrom, $dateTo) {
            $q->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);
        }]);

        if ($providerId) {
            $query->where('id', $providerId);
        }

        $providers = $query->get();
        $commissionRate = $this->getAdminCommission();

        return $providers->map(function($provider) use ($commissionRate, $period, $dateFrom, $dateTo) {
            $appointments = collect();
            
            // Collect all appointments from all provider types
            foreach ($provider->providerTypes as $providerType) {
                $appointments = $appointments->merge($providerType->appointments);
            }

            // Group appointments by period
            $groupedAppointments = $this->groupAppointmentsByPeriod($appointments, $period);
            
            // Calculate metrics for each period
            $periodData = $groupedAppointments->map(function($periodAppointments, $periodKey) use ($commissionRate) {
                $totalAmount = $periodAppointments->sum('total_prices');
                $commission = ($totalAmount * $commissionRate) / 100;
                $providerEarnings = $totalAmount - $commission;
                
                return [
                    'period' => $periodKey,
                    'appointments_count' => $periodAppointments->count(),
                    'total_amount' => $totalAmount,
                    'commission' => $commission,
                    'provider_earnings' => $providerEarnings,
                    'appointments' => $periodAppointments->map(function($appointment) use ($commissionRate) {
                        $commission = ($appointment->total_prices * $commissionRate) / 100;
                        return [
                            'id' => $appointment->id,
                            'date' => $appointment->date,
                            'payment_type' => $appointment->payment_type,
                            'total' => $appointment->total_prices,
                            'commission' => round($commission, 2),
                            'provider_earnings' => round($appointment->total_prices - $commission, 2),
                        ];
                    })
                ];
            });

            // Calculate totals
            $totalAppointments = $appointments->count();
            $totalAmount = $appointments->sum('total_prices');
            $totalCommission = ($totalAmount * $commissionRate) / 100;
            $totalProviderEarnings = $totalAmount - $totalCommission;

            // Get wallet transactions summary
            $walletIn = $provider->walletTransactions
                ->where('type_of_transaction', 1)
                ->sum('amount');
            
            $walletOut = $provider->walletTransactions
                ->where('type_of_transaction', 2)
                ->sum('amount');

            return [
                'provider' => $provider,
                'period_data' => $periodData,
                'summary' => [
                    'total_appointments' => $totalAppointments,
                    'total_amount' => $totalAmount,
                    'total_commission' => $totalCommission,
                    'total_provider_earnings' => $totalProviderEarnings,
                    'wallet_balance' => $provider->balance,
                    'wallet_transactions_in' => $walletIn,
                    'wallet_transactions_out' => $walletOut,
                    'net_wallet_change' => $walletIn - $walletOut,
                ]
            ];
        });
    }

    private function groupAppointmentsByPeriod($appointments, $period)
    {
        switch ($period) {
            case 'yearly':
                return $appointments->groupBy(function($appointment) {
                    return Carbon::parse($appointment->date)->format('Y');
                });
            case 'monthly':
                return $appointments->groupBy(function($appointment) {
                    return Carbon::parse($appointment->date)->format('Y-M');
                });
            default: // daily
                return $appointments->groupBy(function($appointment) {
                    return Carbon::parse($appointment->date)->format('Y-m-d');
                });
        }
    }

    private function calculateSummary($providers)
    {
        return [
            'total_providers' => $providers->count(),
            'total_appointments' => $providers->sum('summary.total_appointments'),
            'total_amount' => $providers->sum('summary.total_amount'),
            'total_commission' => $providers->sum('summary.total_commission'),
            'total_provider_earnings' => $providers->sum('summary.total_provider_earnings'),
            'total_wallet_balance' => $providers->sum('summary.wallet_balance'),
            'total_wallet_in' => $providers->sum('summary.wallet_transactions_in'),
            'total_wallet_out' => $providers->sum('summary.wallet_transactions_out'),
        ];
    }

    private function getAdminCommission()
    {
        $setting = Setting::where('key', 'commission_of_admin')->first();
        return $setting ? $setting->value : 1.5; // Default 1.5%
    }


}
