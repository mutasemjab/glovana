<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use App\Models\ProviderDeleteRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProviderDeleteRequestController extends Controller
{
    /**
     * Display a listing of provider delete requests.
     */
    public function index()
    {
        $deleteRequests = ProviderDeleteRequest::with([
            'provider.providerTypes.ratings',
            'provider.appointments',
            'provider.walletTransactions' => function ($query) {
                $query->orderBy('created_at', 'desc')->limit(5);
            },
            'provider.pointsTransactions' => function ($query) {
                $query->orderBy('created_at', 'desc')->limit(5);
            }
        ])
            ->where('status', 'pending') // Only show pending requests
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.provider-delete-requests.index', compact('deleteRequests'));
    }

    /**
     * Approve a provider delete request.
     */
    public function approve(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $deleteRequest = ProviderDeleteRequest::findOrFail($id);
            $provider = $deleteRequest->provider;

            if ($deleteRequest->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.request_already_processed')
                ], 400);
            }

            // Check if provider has pending appointments
            $pendingAppointments = $provider->appointments()
                ->whereIn('appointment_status', [1, 2, 3, 6, 7]) // Pending, Accepted, OnTheWay, StartWork, Arrived
                ->count();

            if ($pendingAppointments > 0) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.cannot_delete_provider_with_pending_appointments', ['count' => $pendingAppointments])
                ], 400);
            }

            // Check if provider has positive balance
            if ($provider->balance > 0) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.cannot_delete_provider_with_positive_balance', ['balance' => $provider->balance])
                ], 400);
            }

            // Update delete request status
            $deleteRequest->update([
                'status' => 'approved',
                'processed_by' => auth()->id(),
                'processed_at' => now(),
                'admin_notes' => $request->input('admin_notes')
            ]);

            $this->notifyProviderOfApproval($deleteRequest);

            // Delete the provider (this will cascade delete related records)
            $provider->delete();

            DB::commit();

            // Log the deletion
            Log::info('Provider deleted successfully', [
                'provider_id' => $provider->id,
                'provider_name' => $provider->name_of_manager,
                'deleted_by' => auth()->id(),
                'delete_request_id' => $deleteRequest->id
            ]);

            return response()->json([
                'success' => true,
                'message' => __('messages.provider_deleted_successfully')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting provider', [
                'error' => $e->getMessage(),
                'provider_id' => $id,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => __('messages.error_deleting_provider')
            ], 500);
        }
    }

    /**
     * Reject a provider delete request.
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:1000'
        ]);

        try {
            DB::beginTransaction();

            $deleteRequest = ProviderDeleteRequest::findOrFail($id);

            if ($deleteRequest->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.request_already_processed')
                ], 400);
            }

            // Update delete request status
            $deleteRequest->update([
                'status' => 'rejected',
                'processed_by' => auth()->id(),
                'processed_at' => now(),
                'rejection_reason' => $request->input('reason'),
                'admin_notes' => $request->input('admin_notes')
            ]);

            
            DB::commit();

            // You might want to send a notification to the provider here
            $this->notifyProviderOfRejection($deleteRequest);


            Log::info('Provider delete request rejected', [
                'provider_id' => $deleteRequest->provider_id,
                'rejected_by' => auth()->id(),
                'delete_request_id' => $deleteRequest->id,
                'reason' => $request->input('reason')
            ]);

            return response()->json([
                'success' => true,
                'message' => __('messages.delete_request_rejected_successfully')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error rejecting delete request', [
                'error' => $e->getMessage(),
                'delete_request_id' => $id,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => __('messages.error_rejecting_request')
            ], 500);
        }
    }



    /**
     * Send notification to provider about rejection.
     */
    private function notifyProviderOfRejection(ProviderDeleteRequest $deleteRequest)
    {
        try {
            $provider = $deleteRequest->provider;

            // Prepare notification content
            $title = __('messages.delete_request_rejected_title');
            $body = __('messages.delete_request_rejected_body', [
                'provider_name' => $provider->name_of_manager,
                'date' => $deleteRequest->processed_at->format('M d, Y H:i')
            ]);

            // Send FCM notification to provider (no additional data)
            $notificationSent = FCMController::sendMessageToProvider($title, $body, $provider->id);


            // Log successful notification
            \Log::info("Delete request rejection notification sent to provider", [
                'provider_id' => $provider->id,
                'provider_name' => $provider->name_of_manager,
                'delete_request_id' => $deleteRequest->id,
                'rejection_reason' => $deleteRequest->rejection_reason,
                'fcm_sent' => $notificationSent
            ]);

            // Reactivate provider account since deletion was rejected
            $provider->update(['activate' => 1]); // Set back to active

        } catch (\Exception $e) {
            \Log::error("Failed to send delete request rejection notification to provider", [
                'error' => $e->getMessage(),
                'provider_id' => $deleteRequest->provider_id,
                'delete_request_id' => $deleteRequest->id,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }



    /**
     * Send notification when delete request is approved
     */
    private function notifyProviderOfApproval(ProviderDeleteRequest $deleteRequest)
    {
        try {
            $provider = $deleteRequest->provider;

            $title = __('messages.delete_request_approved_title');
            $body = __('messages.delete_request_approved_body', [
                'provider_name' => $provider->name_of_manager
            ]);

            // Send FCM notification (no additional data)
            $notificationSent = FCMController::sendMessageToProvider($title, $body, $provider->id);


            \Log::info("Delete request approval notification sent to provider", [
                'provider_id' => $provider->id,
                'provider_name' => $provider->name_of_manager,
                'delete_request_id' => $deleteRequest->id,
                'fcm_sent' => $notificationSent
            ]);
        } catch (\Exception $e) {
            \Log::error("Failed to send delete request approval notification", [
                'error' => $e->getMessage(),
                'provider_id' => $deleteRequest->provider_id,
                'delete_request_id' => $deleteRequest->id
            ]);
        }
    }

    // /**
    //  * Send final goodbye email when deletion is approved
    //  */
    // private function sendApprovalEmail(ProviderDeleteRequest $deleteRequest)
    // {
    //     try {
    //         $provider = $deleteRequest->provider;

    //         \Mail::send('emails.provider.delete-request-approved', [
    //             'provider' => $provider,
    //             'deleteRequest' => $deleteRequest,
    //             'processedAt' => $deleteRequest->processed_at->format('F d, Y \a\t H:i')
    //         ], function ($message) use ($provider) {
    //             $message->to($provider->email, $provider->name_of_manager)
    //                    ->subject(__('messages.account_deletion_confirmed_subject'));
    //         });

    //         \Log::info("Delete request approval email sent to provider", [
    //             'provider_id' => $provider->id,
    //             'email' => $provider->email,
    //             'delete_request_id' => $deleteRequest->id
    //         ]);

    //     } catch (\Exception $e) {
    //         \Log::error("Failed to send delete request approval email", [
    //             'error' => $e->getMessage(),
    //             'provider_id' => $deleteRequest->provider_id
    //         ]);
    //     }
    // }




    /**
     * Get provider statistics for dashboard/reports.
     */
    public function getStatistics()
    {
        $stats = [
            'pending_requests' => ProviderDeleteRequest::where('status', 'pending')->count(),
            'approved_today' => ProviderDeleteRequest::where('status', 'approved')
                ->whereDate('processed_at', today())->count(),
            'rejected_today' => ProviderDeleteRequest::where('status', 'rejected')
                ->whereDate('processed_at', today())->count(),
            'total_processed_this_month' => ProviderDeleteRequest::whereIn('status', ['approved', 'rejected'])
                ->whereMonth('processed_at', now()->month)
                ->whereYear('processed_at', now()->year)
                ->count()
        ];

        return response()->json($stats);
    }
}
