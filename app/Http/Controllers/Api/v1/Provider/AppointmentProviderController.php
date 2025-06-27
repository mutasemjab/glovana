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
use App\Models\ParentStudent;
use App\Traits\Responses;
use Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class AppointmentProviderController extends Controller
{
     use Responses;
    public function getProviderAppointments(Request $request)
    {
        $provider = auth()->user();
        
        if (!$provider instanceof \App\Models\Provider) {
            return $this->error_response('Unauthorized', 'Only providers can view appointments');
        }

        $validator = Validator::make($request->all(), [
            'status' => 'nullable|in:1,2,3,4,5', // Filter by status
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'per_page' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1'
        ]);

        if ($validator->fails()) {
            return $this->error_response('Validation error', $validator->errors());
        }

        $query = \App\Models\Appointment::whereHas('providerType', function($q) use ($provider) {
            $q->where('provider_id', $provider->id);
        })->with([
            'user:id,name,phone,email,photo',
            'address',
            'providerType',
            'providerType.type'
        ]);

        // Filter by status if provided
        if ($request->filled('status')) {
            $query->where('appointment_status', $request->status);
        }

        // Filter by date range if provided
        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        // Order by date (newest first)
        $query->orderBy('date', 'desc');

        $perPage = $request->get('per_page', 15);
        $appointments = $query->paginate($perPage);

        // Add status text for easy reading
        $appointments->getCollection()->transform(function ($appointment) {
            $appointment->status_text = $this->getAppointmentStatusText($appointment->appointment_status);
            $appointment->payment_status_text = $appointment->payment_status == 1 ? 'Paid' : 'Unpaid';
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

        $appointment = \App\Models\Appointment::whereHas('providerType', function($q) use ($provider) {
            $q->where('provider_id', $provider->id);
        })->with([
            'user:id,name,phone,email,photo',
            'address',
            'providerType',
            'providerType.type',
            'providerType.provider:id,name_of_manager,phone'
        ])->find($appointmentId);

        if (!$appointment) {
            return $this->error_response('Not found', 'Appointment not found');
        }

        $appointment->status_text = $this->getAppointmentStatusText($appointment->appointment_status);
        $appointment->payment_status_text = $appointment->payment_status == 1 ? 'Paid' : 'Unpaid';

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
            'status' => 'required|in:2,3,4,5', // Can't set to pending (1), only accept, on the way, delivered, or cancel
            'note' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return $this->error_response('Validation error', $validator->errors());
        }

        $appointment = \App\Models\Appointment::whereHas('providerType', function($q) use ($provider) {
            $q->where('provider_id', $provider->id);
        })->find($appointmentId);

        if (!$appointment) {
            return $this->error_response('Not found', 'Appointment not found');
        }

        // Check if status transition is valid
        $currentStatus = $appointment->appointment_status;
        $newStatus = $request->status;
        
        if (!$this->isValidStatusTransition($currentStatus, $newStatus)) {
            return $this->error_response('Invalid status transition', 
                'Cannot change from ' . $this->getAppointmentStatusText($currentStatus) . 
                ' to ' . $this->getAppointmentStatusText($newStatus));
        }

        $appointment->appointment_status = $newStatus;
        
        if ($request->filled('note')) {
            $appointment->note = $request->note;
        }

        $appointment->save();

         $this->sendAppointmentStatusNotificationToUser($appointment, $currentStatus, $newStatus);


        return $this->success_response('Appointment status updated successfully', [
            'appointment' => $appointment->load('user', 'providerType'),
            'status_text' => $this->getAppointmentStatusText($newStatus)
        ]);
    }


    private function sendNewAppointmentNotificationToProvider($appointment, $provider)
{
    try {
        $user = $appointment->user;
        $appointmentDate = \Carbon\Carbon::parse($appointment->date)->format('M d, Y H:i');
        
        $title = "New Appointment Request";
        $body = "New appointment from {$user->name} scheduled for {$appointmentDate}. Appointment #{$appointment->number}";
        
        // Send FCM notification to provider
         FCMController::sendMessageToProvider($title, $body, $provider->id);
        
        \Log::info("New appointment notification sent to provider ID: {$provider->id} for appointment ID: {$appointment->id}");
    } catch (\Exception $e) {
        \Log::error("Failed to send new appointment notification to provider: " . $e->getMessage());
    }
}

// Helper method to send notification to user when appointment status is updated
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
        
        // Send FCM notification to user
        FCMController::sendMessageToUser($title, $body, $appointment->user_id);
        
        \Log::info("Appointment status notification sent to user ID: {$appointment->user_id} for appointment ID: {$appointment->id}");
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
            5 => 'Canceled'
        ];

        return $statuses[$status] ?? 'Unknown';
    }

    private function isValidStatusTransition($currentStatus, $newStatus)
    {
        // Define valid status transitions
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