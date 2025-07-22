<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\NoteVoucher;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Setting;
use App\Models\Shop;
use Illuminate\Http\Request;

class PaymentReportController extends Controller
{
    public function paymentReport(Request $request)
    {
        $query = Appointment::with('providerType.provider');

        if ($request->filled('provider_id')) {
            $query->whereHas('providerType', fn($q) =>
                $q->where('provider_id', $request->provider_id)
            );
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        if ($request->filled('payment_type')) {
            $query->where('payment_type', $request->payment_type);
        }

        $appointments = $query->get();

        $commissionRate = $this->getAdminCommission();
        $report = [
            'appointments' => [],
            'total_amount' => 0,
            'total_commission' => 0,
            'total_provider_earnings' => 0,
        ];

        foreach ($appointments as $appointment) {
            $commission = ($appointment->total_prices * $commissionRate) / 100;
            $providerEarnings = $appointment->total_prices - $commission;

            $report['total_amount'] += $appointment->total_prices;
            $report['total_commission'] += $commission;
            $report['total_provider_earnings'] += $providerEarnings;

            $report['appointments'][] = [
                'id' => $appointment->id,
                'date' => $appointment->date,
                'provider' => $appointment->providerType->provider->name_of_manager ?? 'Unknown',
                'payment_type' => $appointment->payment_type,
                'payment_status' => $appointment->payment_status == 1 ? 'Paid' : 'Unpaid',
                'total' => $appointment->total_prices,
                'commission' => round($commission, 2),
                'provider_earnings' => round($providerEarnings, 2),
            ];
        }

        return view('reports.payment', compact('report', 'appointments', 'request'));
    }


     private function getAdminCommission()
    {
        $setting = Setting::where('key', 'commission_of_admin')->first();
        return $setting ? $setting->value : 1.5; // Default 1.5%
    }

}
