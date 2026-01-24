<?php

namespace App\Http\Controllers\Api\v1\Provider;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use App\Models\ClassTeacher;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Admin\FCMController; // <-- Import the FCMController here
use App\Models\Appointment;
use App\Models\ParentStudent;
use App\Models\Setting;
use App\Models\WalletTransaction;
use App\Services\PointsService;
use App\Traits\Responses;
use Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class AppointmentProviderController extends Controller
{
    use Responses;

    protected $pointsService; 

    public function __construct(PointsService $pointsService)
    {
        $this->pointsService = $pointsService;
    }

    public function paymentReport(Request $request)
    {
        $provider = auth()->user();

        if (!$provider instanceof \App\Models\Provider) {
            return $this->error_response('Unauthorized', 'Only providers can view reports');
        }

        $validator = Validator::make($request->all(), [
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'payment_type' => 'nullable|in:cash,visa,wallet',
            'appointment_status' => 'nullable|in:1,2,3,4,5',
            'payment_status' => 'nullable|in:1,2',
            'settlement_status' => 'nullable|in:1,2', // 1 = pending, 2 = settled
            'settlement_cycle_id' => 'nullable|exists:settlement_cycles,id',
        ]);

        if ($validator->fails()) {
            return $this->error_response('Validation error', $validator->errors());
        }

        $appointments = Appointment::where('appointment_status',4)->whereHas('providerType', function ($q) use ($provider) {
            $q->where('provider_id', $provider->id);
        })
            ->when($request->filled('date_from'), fn($q) => $q->whereDate('date', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn($q) => $q->whereDate('date', '<=', $request->date_to))
            ->when($request->filled('payment_type'), fn($q) => $q->where('payment_type', $request->payment_type))
            ->when($request->filled('appointment_status'), fn($q) => $q->where('appointment_status', $request->appointment_status))
            ->when($request->filled('payment_status'), fn($q) => $q->where('payment_status', $request->payment_status))
            ->when($request->filled('settlement_status'), fn($q) => $q->where('settlement_status', $request->settlement_status))
            ->when($request->filled('settlement_cycle_id'), fn($q) => $q->where('settlement_cycle_id', $request->settlement_cycle_id))
            ->with(['providerType.provider', 'settlementCycle', 'appointmentSettlement'])
            ->get();

        $commissionRate = $this->getAdminCommission();

        // Get settlement cycles info
        $settlementCycles = \App\Models\SettlementCycle::whereHas('appointmentSettlements', function ($q) use ($provider) {
            $q->where('provider_id', $provider->id);
        })
            ->when($request->filled('date_from'), fn($q) => $q->where('end_date', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn($q) => $q->where('start_date', '<=', $request->date_to))
            ->with(['providerSettlements' => function ($q) use ($provider) {
                $q->where('provider_id', $provider->id);
            }])
            ->orderBy('start_date', 'desc')
            ->get();

        // Calculate totals by payment type
        $totalsByPaymentType = [
            'cash' => ['count' => 0, 'amount' => 0, 'commission' => 0, 'net' => 0],
            'visa' => ['count' => 0, 'amount' => 0, 'commission' => 0, 'net' => 0],
            'wallet' => ['count' => 0, 'amount' => 0, 'commission' => 0, 'net' => 0],
        ];

        $report = [
            'total_appointments' => $appointments->count(),
            'total_amount' => 0,
            'total_commission' => 0,
            'total_provider_earnings' => 0,
            'appointments' => [],
        ];

        foreach ($appointments as $appointment) {
            $commission = ($appointment->total_prices * $commissionRate) / 100;
            $providerEarnings = $appointment->total_prices - $commission;

            $report['total_amount'] += $appointment->total_prices;
            $report['total_commission'] += $commission;
            $report['total_provider_earnings'] += $providerEarnings;

            // Add to payment type totals
            $paymentType = $appointment->payment_type;
            if (isset($totalsByPaymentType[$paymentType])) {
                $totalsByPaymentType[$paymentType]['count']++;
                $totalsByPaymentType[$paymentType]['amount'] += $appointment->total_prices;
                $totalsByPaymentType[$paymentType]['commission'] += $commission;
                $totalsByPaymentType[$paymentType]['net'] += $providerEarnings;
            }

            $report['appointments'][] = [
                'id' => $appointment->id,
                'number' => $appointment->number,
                'date' => $appointment->date,
                'status' => $this->getAppointmentStatusText($appointment->appointment_status),
                'payment_type' => $appointment->payment_type,
                'payment_status' => $appointment->payment_status == 1 ? 'Paid' : 'Unpaid',
                'settlement_status' => $appointment->settlement_status == 1 ? 'Pending Settlement' : 'Settled',
                'settlement_cycle' => $appointment->settlementCycle ? [
                    'id' => $appointment->settlementCycle->id,
                    'period' => $appointment->settlementCycle->start_date->format('d M') . ' - ' . $appointment->settlementCycle->end_date->format('d M Y'),
                    'status' => $appointment->settlementCycle->status == 1 ? 'Active' : 'Completed'
                ] : null,
                'total' => $appointment->total_prices,
                'commission' => round($commission, 2),
                'provider_earnings' => round($providerEarnings, 2),
            ];
        }

        // Add payment type breakdown
        $report['payment_type_breakdown'] = $totalsByPaymentType;

        // Add settlement cycles summary
        $report['settlement_cycles'] = $settlementCycles->map(function ($cycle) use ($commissionRate) {
            $providerSettlement = $cycle->providerSettlements->first();

            return [
                'id' => $cycle->id,
                'period' => $cycle->start_date->format('d M') . ' - ' . $cycle->end_date->format('d M Y'),
                'start_date' => $cycle->start_date->format('Y-m-d'),
                'end_date' => $cycle->end_date->format('Y-m-d'),
                'status' => $cycle->status == 1 ? 'Active' : 'Completed',
                'total_appointments' => $providerSettlement ? $providerSettlement->total_appointments : 0,
                'total_amount' => $providerSettlement ? $providerSettlement->total_appointments_amount : 0,
                'commission' => $providerSettlement ? $providerSettlement->commission_amount : 0,
                'net_amount' => $providerSettlement ? $providerSettlement->net_amount : 0,
                'payment_status' => $providerSettlement ?
                    ($providerSettlement->payment_status == 1 ? 'Pending' : 'Paid') : 'N/A',
            ];
        });

        // Get current active cycle
        $currentCycle = \App\Models\SettlementCycle::where('status', 1)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->first();

        if ($currentCycle) {
            $report['current_settlement_cycle'] = [
                'id' => $currentCycle->id,
                'period' => $currentCycle->start_date->format('d M') . ' - ' . $currentCycle->end_date->format('d M Y'),
                'days_remaining' => now()->diffInDays($currentCycle->end_date, false) + 1,
                'end_date' => $currentCycle->end_date->format('Y-m-d'),
            ];
        }

        return $this->success_response('Payment report generated', $report);
    }


    public function getProviderAppointments(Request $request)
    {
        $provider = auth()->user();

        if (!$provider instanceof \App\Models\Provider) {
            return $this->error_response('Unauthorized', 'Only providers can view appointments');
        }

        $validator = Validator::make($request->all(), [
            'status' => 'nullable|in:1,2,3,4,5,6,7',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'appointment_type' => 'nullable|in:instant,scheduled', // NEW FILTER
            'per_page' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1'
        ]);

        if ($validator->fails()) {
            return $this->error_response('Validation error', $validator->errors());
        }

        $query = \App\Models\Appointment::whereHas('providerType', function ($q) use ($provider) {
            $q->where('provider_id', $provider->id);
        })->with([
            'user:id,name,phone,email,photo',
            'address',
            'providerType',
            'providerType.type',
            'appointmentServices.service'
        ]);

        if ($request->filled('status')) {
            $query->where('appointment_status', $request->status);
        }

        // NEW: Filter by appointment type
        if ($request->filled('appointment_type')) {
            $query->where('appointment_type', $request->appointment_type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        $query->orderBy('date', 'desc');

        $perPage = $request->get('per_page', 15);
        $appointments = $query->paginate($perPage);

        $appointments->getCollection()->transform(function ($appointment) {
            $appointment->status_text = $this->getAppointmentStatusText($appointment->appointment_status);
            $appointment->payment_status_text = $appointment->payment_status == 1 ? 'Paid' : 'Unpaid';
            $appointment->appointment_type_text = ucfirst($appointment->appointment_type); // NEW
            $appointment->booking_type = $appointment->providerType->type->booking_type ?? 'hourly';
            $appointment->total_customers = $this->getTotalCustomers($appointment);
            $appointment->can_finish = $appointment->appointment_status == 3;
            $appointment->requires_payment_confirmation = ($appointment->appointment_status == 4 && $appointment->payment_status == 2);
            return $appointment;
        });

        return $this->success_response('Appointments retrieved successfully', [
            'appointments' => $appointments,
        ]);
    }

    public function getAppointmentDetails(Request $request, $appointmentId)
    {
        $provider = auth()->user();

        if (!$provider instanceof \App\Models\Provider) {
            return $this->error_response('Unauthorized', 'Only providers can view appointment details');
        }

        $appointment = \App\Models\Appointment::whereHas('providerType', function ($q) use ($provider) {
            $q->where('provider_id', $provider->id);
        })->with([
            'user:id,name,phone,email,photo,balance',
            'address',
            'providerType',
            'providerType.type',
            'providerType.provider:id,name_of_manager,phone',
            'appointmentServices.service'
        ])->find($appointmentId);

        if (!$appointment) {
            return $this->error_response('Not found', 'Appointment not found');
        }

        $appointment->status_text = $this->getAppointmentStatusText($appointment->appointment_status);
        $appointment->payment_status_text = $appointment->payment_status == 1 ? 'Paid' : 'Unpaid';
        $appointment->booking_type = $appointment->providerType->type->booking_type ?? 'hourly';
        $appointment->total_customers = $this->getTotalCustomers($appointment);
        $appointment->can_finish = $appointment->appointment_status == 3;
        $appointment->requires_payment_confirmation = ($appointment->appointment_status == 4 && $appointment->payment_status == 2);

        // Add services summary for service-based appointments
        if ($appointment->booking_type == 'service') {
            $appointment->services_summary = $this->getServicesSummary($appointment);
        }

        // Add payment options info
        $appointment->payment_options = [
            'can_pay_with_wallet' => $appointment->user->balance >= $appointment->total_prices,
            'user_wallet_balance' => $appointment->user->balance,
            'required_amount' => $appointment->total_prices
        ];

        return $this->success_response('Appointment details retrieved successfully', [
            'appointment' => $appointment
        ]);
    }

    public function updateAppointmentStatus(Request $request, $appointmentId)
    {
        $provider = auth()->user();

        if (!$provider instanceof \App\Models\Provider) {
            return $this->error_response('Unauthorized', 'Only providers can update appointment status');
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:2,3,4,5,6,7',
            'note' => 'nullable|string|max:500',
            'reason_of_cancel' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return $this->error_response('Validation error', $validator->errors());
        }

        $appointment = \App\Models\Appointment::whereHas('providerType', function ($q) use ($provider) {
            $q->where('provider_id', $provider->id);
        })->with(['user', 'providerType.provider', 'providerType'])->find($appointmentId);

        if (!$appointment) {
            return $this->error_response('Not found', 'Appointment not found');
        }

        $currentStatus = $appointment->appointment_status;
        $newStatus = $request->status;

        // Handle completing appointment (status 4)
        if ($newStatus == 4) {
            try {
                DB::beginTransaction();

                // Update appointment status
                $appointment->appointment_status = 4;
                $appointment->payment_status = 1;

                if ($request->filled('note')) {
                    $appointment->note = $request->note;
                }

                $appointment->save();

                // Record in appointment_settlements for tracking
                $this->recordAppointmentForSettlement($appointment);

                // ✅ ✅ منح النقاط - كود بسيط وواضح ✅ ✅
                if ($appointment->points_awarded != 1) {
                    $user = $appointment->user;
                    
                    if ($user) {
                        try {
                            // منح نقاط الصالون (سيمنح VIP تلقائياً إذا كان موجود)
                            $transaction = $this->pointsService->awardSalonBookingPoints($user, $appointment);
                            
                            if ($transaction) {
                                // تحديث appointment بعدد النقاط الممنوحة
                                $appointment->points_earned = $transaction->points;
                                $appointment->points_awarded = 1;
                                $appointment->save();
                                
                                \Log::info("Points awarded for appointment", [
                                    'appointment_id' => $appointment->id,
                                    'user_id' => $user->id,
                                    'points' => $transaction->points,
                                    'is_vip' => $appointment->providerType->is_vip ?? 0
                                ]);
                            }
                        } catch (\Exception $e) {
                            \Log::error("Failed to award points: " . $e->getMessage());
                            // لا نوقف العملية إذا فشلت النقاط
                        }
                    }
                }

                DB::commit();

                // Send notification to user
                $this->sendAppointmentCompletedNotificationToUser($appointment);

                $message = 'Appointment completed successfully. Payment will be settled in the next cycle.';
                if ($appointment->points_earned > 0) {
                    $message .= " User earned {$appointment->points_earned} points!";
                }

                return $this->success_response($message, [
                    'appointment' => $appointment,
                    'status_text' => $this->getAppointmentStatusText($newStatus),
                    'payment_type' => $appointment->payment_type,
                    'points_earned' => $appointment->points_earned
                ]);
            } catch (\Exception $e) {
                DB::rollback();
                return $this->error_response('Failed to complete appointment', ['error' => $e->getMessage()]);
            }
        }

        // For other status changes
        $appointment->appointment_status = $newStatus;

        if ($request->filled('note')) {
            $appointment->note = $request->note;
        }
        if ($request->filled('reason_of_cancel')) {
            $appointment->reason_of_cancel = $request->reason_of_cancel;
        }

        $appointment->save();

        $this->sendAppointmentStatusNotificationToUser($appointment, $currentStatus, $newStatus);

        return $this->success_response('Appointment status updated successfully', [
            'appointment' => $appointment,
            'status_text' => $this->getAppointmentStatusText($newStatus)
        ]);
    }

    /**
     * Record appointment for settlement cycle
     */
    private function recordAppointmentForSettlement($appointment)
    {
        $commission = $this->getAdminCommission();
        $commissionAmount = ($appointment->total_prices * $commission) / 100;
        $providerAmount = $appointment->total_prices - $commissionAmount;

        // Get or create current settlement cycle
        $settlementCycle = $this->getCurrentSettlementCycle();

        // Create appointment settlement record
        \App\Models\AppointmentSettlement::create([
            'settlement_cycle_id' => $settlementCycle->id,
            'appointment_id' => $appointment->id,
            'provider_id' => $appointment->providerType->provider->id,
            'appointment_amount' => $appointment->total_prices,
            'commission_amount' => $commissionAmount,
            'provider_amount' => $providerAmount,
            'payment_type' => $appointment->payment_type,
        ]);

        // Link appointment to settlement cycle
        $appointment->settlement_cycle_id = $settlementCycle->id;
        $appointment->settlement_status = 1; // Pending settlement
        $appointment->save();
    }

    /**
     * Get or create current settlement cycle (every 14 days)
     */
    private function getCurrentSettlementCycle()
    {
        $today = now();

        // Check if there's an active cycle
        $activeCycle = \App\Models\SettlementCycle::where('status', 1)
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->first();

        if ($activeCycle) {
            return $activeCycle;
        }

        // Create new cycle based on day of month
        $dayOfMonth = $today->day;

        if ($dayOfMonth <= 14) {
            // First cycle of the month (1-14)
            $startDate = $today->copy()->startOfMonth();
            $endDate = $today->copy()->startOfMonth()->addDays(13); // Day 14
        } else {
            // Second cycle of the month (15-end)
            $startDate = $today->copy()->startOfMonth()->addDays(14); // Day 15
            $endDate = $today->copy()->endOfMonth();
        }

        return \App\Models\SettlementCycle::create([
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => 1, // Pending
        ]);
    }

    /**
     * Send appointment completed notification to user
     */
     private function sendAppointmentCompletedNotificationToUser($appointment)
    {
        try {
            $title = "Appointment Completed";
            $body = "Your appointment #{$appointment->number} has been completed. Payment method: {$appointment->payment_type}. Settlement will be processed in the next cycle.";
            
            // ✅ إضافة معلومات النقاط إلى الإشعار
            if ($appointment->points_earned > 0) {
                $body .= " You earned {$appointment->points_earned} points! 🎉";
            }

            FCMController::sendMessageToUser($title, $body, $appointment->user_id);
            \Log::info("Appointment completed notification sent to user ID: {$appointment->user_id}");
        } catch (\Exception $e) {
            \Log::error("Failed to send appointment completed notification: " . $e->getMessage());
        }
    }




    /**
     * Get total customers for an appointment
     */
    private function getTotalCustomers($appointment)
    {
        if (
            isset($appointment->providerType->type->booking_type) &&
            $appointment->providerType->type->booking_type == 'service'
        ) {
            return $appointment->appointmentServices->sum('customer_count');
        }
        return 1; // Default for hourly appointments
    }

    /**
     * Get services summary for service-based appointments including individual customer services
     */
    private function getServicesSummary($appointment)
    {
        if (
            isset($appointment->providerType->type->booking_type) &&
            $appointment->providerType->type->booking_type == 'service'
        ) {

            // Get aggregated services (existing functionality)
            $services = $appointment->appointmentServices->map(function ($appointmentService) {
                return [
                    'name' => app()->getLocale() == 'ar' ?
                        $appointmentService->service->name_ar :
                        $appointmentService->service->name_en,
                    'customer_count' => $appointmentService->customer_count,
                    'service_price' => $appointmentService->service_price,
                    'total_price' => $appointmentService->total_price
                ];
            });

            // Get individual customer services grouped by person
            $customerServices = $appointment->appointmentServices
                ->groupBy('person_number')
                ->map(function ($services, $personNumber) {
                    return [
                        'person_number' => $personNumber,
                        'total_services' => $services->count(),
                        'total_amount' => $services->sum('service_price'),
                        'services' => $services->map(function ($service) {
                            return [
                                'service_id' => $service->service_id,
                                'service_name' => app()->getLocale() == 'ar' ?
                                    $service->service->name_ar :
                                    $service->service->name_en,
                                'service_price' => $service->service_price
                            ];
                        })->toArray()
                    ];
                })
                ->values()
                ->toArray();

            return [
                'services' => $services,
                'total_services' => $services->count(),
                'total_customers' => $services->sum('customer_count'),
                'services_total' => $services->sum('total_price'),
                'customer_services' => $customerServices // Individual customer breakdown
            ];
        }

        return null;
    }

    /**
     * Get admin commission from settings
     */
    private function getAdminCommission()
    {
        $setting = Setting::where('key', 'commission_of_admin')->first();
        return $setting ? $setting->value : 1.5; // Default 1.5%
    }



    private function sendAppointmentStatusNotificationToUser($appointment, $oldStatus, $newStatus)
    {
        try {
            $statusMessages = [
                2 => 'Your appointment has been accepted by the provider',
                3 => 'Your provider is on the way',
                4 => 'Your appointment has been completed',
                5 => 'Your appointment has been cancelled'
            ];

            $title = "Appointment Status Update";
            $body = $statusMessages[$newStatus] ?? "Your appointment status has been updated";
            $body .= " - Appointment #{$appointment->number}";

            // Add cancellation reason if status is 5 and reason exists
            if ($newStatus == 5 && !empty($appointment->reason_of_cancel)) {
                $body .= ". Reason: {$appointment->reason_of_cancel}";
            }

            FCMController::sendMessageToUser($title, $body, $appointment->user_id);
            \Log::info("Appointment status notification sent to user ID: {$appointment->user_id}");
        } catch (\Exception $e) {
            \Log::error("Failed to send appointment status notification to user: " . $e->getMessage());
        }
    }
    // Helper Methods
    private function getAppointmentStatusText($status)
    {
        $statuses = [
            1 => 'Pending',
            2 => 'Accepted',
            3 => 'On The Way',
            4 => 'Delivered',
            5 => 'Canceled',
            6 => 'Start work',
            7 => 'User arrived to provider',
        ];

        return $statuses[$status] ?? 'Unknown';
    }

    private function isValidStatusTransition($currentStatus, $newStatus)
    {
        $validTransitions = [
            1 => [2, 5], // From Pending: can go to Accepted or Canceled
            2 => [3, 5], // From Accepted: can go to On The Way or Canceled
            3 => [4, 5], // From On The Way: can go to Delivered or Canceled
            4 => [],     // From Delivered: no transitions allowed
            5 => []      // From Canceled: no transitions allowed
        ];

        return in_array($newStatus, $validTransitions[$currentStatus] ?? []);
    }
}
