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
        $period = $request->get('period', 'daily');
        $providerId = $request->get('provider_id');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $settlementCycleId = $request->get('settlement_cycle_id');
        $settlementStatus = $request->get('settlement_status'); // 1=pending, 2=settled
        
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

        $providers = $this->getProvidersReport($providerId, $dateFrom, $dateTo, $period, $settlementCycleId, $settlementStatus);
        $summary = $this->calculateSummary($providers);
        $settlementCycles = $this->getSettlementCycles($dateFrom, $dateTo);
        $currentCycle = $this->getCurrentCycle();
        
        // Get all providers for filter
        $allProviders = Provider::select('id', 'name_of_manager')->get();
        
        return view('reports.payment', compact(
            'providers',
            'summary',
            'settlementCycles',
            'currentCycle',
            'allProviders',
            'request',
            'period',
            'dateFrom',
            'dateTo',
            'settlementCycleId',
            'settlementStatus'
        ));
    }

    private function getProvidersReport($providerId, $dateFrom, $dateTo, $period, $settlementCycleId = null, $settlementStatus = null)
    {
        $query = Provider::with([
            'providerTypes.appointments' => function($q) use ($dateFrom, $dateTo, $settlementCycleId, $settlementStatus) {
                $q->whereBetween('date', [$dateFrom, $dateTo])
                  ->where('payment_status', 1); // Only paid appointments
                
                if ($settlementCycleId) {
                    $q->where('settlement_cycle_id', $settlementCycleId);
                }
                
                if ($settlementStatus) {
                    $q->where('settlement_status', $settlementStatus);
                }
                
                $q->with(['settlementCycle', 'appointmentSettlement', 'user:id,name']);
            },
            'walletTransactions' => function($q) use ($dateFrom, $dateTo) {
                $q->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);
            }
        ]);

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

            // Get settlement cycles for this provider
            $providerSettlements = $this->getProviderSettlements($provider->id, $dateFrom, $dateTo);
            
            // Group appointments by period
            $groupedAppointments = $this->groupAppointmentsByPeriod($appointments, $period);
            
            // Calculate metrics for each period
            $periodData = $groupedAppointments->map(function($periodAppointments, $periodKey) use ($commissionRate) {
                // Calculate totals
                $totalAmount = $periodAppointments->sum('total_prices');
                $totalCommission = 0;
                $totalProviderEarnings = 0;
                
                // Calculate by payment type
                $paymentBreakdown = [
                    'cash' => ['count' => 0, 'amount' => 0, 'commission' => 0],
                    'visa' => ['count' => 0, 'amount' => 0, 'commission' => 0],
                    'wallet' => ['count' => 0, 'amount' => 0, 'commission' => 0],
                ];
                
                foreach ($periodAppointments as $appointment) {
                    $commission = ($appointment->total_prices * $commissionRate) / 100;
                    $providerEarning = $appointment->total_prices - $commission;
                    
                    $totalCommission += $commission;
                    $totalProviderEarnings += $providerEarning;
                    
                    $paymentType = $appointment->payment_type;
                    if (isset($paymentBreakdown[$paymentType])) {
                        $paymentBreakdown[$paymentType]['count']++;
                        $paymentBreakdown[$paymentType]['amount'] += $appointment->total_prices;
                        $paymentBreakdown[$paymentType]['commission'] += $commission;
                    }
                }
                
                return [
                    'period' => $periodKey,
                    'appointments_count' => $periodAppointments->count(),
                    'total_amount' => $totalAmount,
                    'commission' => $totalCommission,
                    'provider_earnings' => $totalProviderEarnings,
                    'payment_breakdown' => $paymentBreakdown,
                    'appointments' => $periodAppointments->map(function($appointment) use ($commissionRate) {
                        $commission = ($appointment->total_prices * $commissionRate) / 100;
                        return [
                            'id' => $appointment->id,
                            'number' => $appointment->number,
                            'date' => $appointment->date,
                            'user_name' => $appointment->user->name ?? 'N/A',
                            'payment_type' => $appointment->payment_type,
                            'total' => $appointment->total_prices,
                            'commission' => round($commission, 2),
                            'provider_earnings' => round($appointment->total_prices - $commission, 2),
                            'settlement_status' => $appointment->settlement_status,
                            'settlement_cycle' => $appointment->settlementCycle ? [
                                'id' => $appointment->settlementCycle->id,
                                'period' => $appointment->settlementCycle->start_date->format('d M') . ' - ' . $appointment->settlementCycle->end_date->format('d M Y'),
                                'status' => $appointment->settlementCycle->status == 1 ? 'active' : 'completed'
                            ] : null,
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

            // Calculate payment type breakdown for entire period
            $overallPaymentBreakdown = [
                'cash' => ['count' => 0, 'amount' => 0, 'commission' => 0, 'net' => 0],
                'visa' => ['count' => 0, 'amount' => 0, 'commission' => 0, 'net' => 0],
                'wallet' => ['count' => 0, 'amount' => 0, 'commission' => 0, 'net' => 0],
            ];
            
            foreach ($appointments as $appointment) {
                $commission = ($appointment->total_prices * $commissionRate) / 100;
                $net = $appointment->total_prices - $commission;
                $paymentType = $appointment->payment_type;
                
                if (isset($overallPaymentBreakdown[$paymentType])) {
                    $overallPaymentBreakdown[$paymentType]['count']++;
                    $overallPaymentBreakdown[$paymentType]['amount'] += $appointment->total_prices;
                    $overallPaymentBreakdown[$paymentType]['commission'] += $commission;
                    $overallPaymentBreakdown[$paymentType]['net'] += $net;
                }
            }

            return [
                'provider' => $provider,
                'period_data' => $periodData,
                'provider_settlements' => $providerSettlements,
                'payment_breakdown' => $overallPaymentBreakdown,
                'summary' => [
                    'total_appointments' => $totalAppointments,
                    'total_amount' => $totalAmount,
                    'total_commission' => $totalCommission,
                    'total_provider_earnings' => $totalProviderEarnings,
                    'wallet_balance' => $provider->balance,
                    'wallet_transactions_in' => $walletIn,
                    'wallet_transactions_out' => $walletOut,
                    'net_wallet_change' => $walletIn - $walletOut,
                    'pending_settlements_count' => $appointments->where('settlement_status', 1)->count(),
                    'settled_appointments_count' => $appointments->where('settlement_status', 2)->count(),
                ]
            ];
        });
    }

    private function getProviderSettlements($providerId, $dateFrom, $dateTo)
    {
        return ProviderSettlement::with('settlementCycle')
            ->where('provider_id', $providerId)
            ->whereHas('settlementCycle', function($q) use ($dateFrom, $dateTo) {
                $q->whereBetween('start_date', [$dateFrom, $dateTo])
                  ->orWhereBetween('end_date', [$dateFrom, $dateTo]);
            })
            ->get()
            ->map(function($settlement) {
                return [
                    'cycle_id' => $settlement->settlement_cycle_id,
                    'period' => $settlement->settlementCycle->start_date->format('d M') . ' - ' . $settlement->settlementCycle->end_date->format('d M Y'),
                    'total_appointments' => $settlement->total_appointments,
                    'total_amount' => $settlement->total_appointments_amount,
                    'commission' => $settlement->commission_amount,
                    'net_amount' => $settlement->net_amount,
                    'payment_status' => $settlement->payment_status,
                    'cycle_status' => $settlement->settlementCycle->status,
                ];
            });
    }

    private function getSettlementCycles($dateFrom, $dateTo)
    {
        return SettlementCycle::whereBetween('start_date', [$dateFrom, $dateTo])
            ->orWhereBetween('end_date', [$dateFrom, $dateTo])
            ->orderBy('start_date', 'desc')
            ->get()
            ->map(function($cycle) {
                return [
                    'id' => $cycle->id,
                    'label' => $cycle->start_date->format('d M') . ' - ' . $cycle->end_date->format('d M Y'),
                    'start_date' => $cycle->start_date->format('Y-m-d'),
                    'end_date' => $cycle->end_date->format('Y-m-d'),
                    'status' => $cycle->status,
                ];
            });
    }

    private function getCurrentCycle()
    {
        $cycle = SettlementCycle::where('status', 1)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->first();

        if ($cycle) {
            return [
                'id' => $cycle->id,
                'period' => $cycle->start_date->format('d M') . ' - ' . $cycle->end_date->format('d M Y'),
                'days_remaining' => now()->diffInDays($cycle->end_date, false) + 1,
                'end_date' => $cycle->end_date->format('Y-m-d'),
            ];
        }

        return null;
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
        $pendingSettlements = 0;
        $completedSettlements = 0;
        
        foreach ($providers as $providerData) {
            $pendingSettlements += $providerData['summary']['pending_settlements_count'];
            $completedSettlements += $providerData['summary']['settled_appointments_count'];
        }
        
        return [
            'total_providers' => $providers->count(),
            'total_appointments' => $providers->sum('summary.total_appointments'),
            'total_amount' => $providers->sum('summary.total_amount'),
            'total_commission' => $providers->sum('summary.total_commission'),
            'total_provider_earnings' => $providers->sum('summary.total_provider_earnings'),
            'total_wallet_balance' => $providers->sum('summary.wallet_balance'),
            'total_wallet_in' => $providers->sum('summary.wallet_transactions_in'),
            'total_wallet_out' => $providers->sum('summary.wallet_transactions_out'),
            'pending_settlements' => $pendingSettlements,
            'completed_settlements' => $completedSettlements,
        ];
    }

    private function getAdminCommission()
    {
        $setting = Setting::where('key', 'commission_of_admin')->first();
        return $setting ? $setting->value : 1.5;
    }
}
