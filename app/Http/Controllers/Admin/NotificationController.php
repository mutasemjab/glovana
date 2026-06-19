<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    public function create()
    {
        $users = User::get();
        return view('admin.notifications.create', compact('users'));
    }

    public function send(Request $request)
    {
        // Validate the input
        $this->validate($request, [
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'type' => 'required|in:0,1,2,3,4', // 0=all, 1=all users, 2=all providers, 3=specific user, 4=specific provider
            'user_id' => 'required_if:type,3|nullable|exists:users,id',
            'provider_id' => 'required_if:type,4|nullable|exists:providers,id',
        ]);

        DB::beginTransaction();

        try {
            $response = false;
            $notificationService = app(NotificationService::class);

            // Determine who to send to based on type
            switch ($request->type) {
                case 0: // Send to ALL (users + providers)
                    $response = $notificationService->notifyAll($request->title, $request->body);
                    break;

                case 1: // Send to ALL Users
                    $response = $notificationService->notifyAllUsers($request->title, $request->body);
                    break;

                case 2: // Send to ALL Providers
                    $response = $notificationService->notifyAllProviders($request->title, $request->body);
                    break;

                case 3: // Send to Specific User
                    $response = $notificationService->notifyUser((int) $request->user_id, $request->title, $request->body);
                    break;

                case 4: // Send to Specific Provider
                    $response = $notificationService->notifyProvider((int) $request->provider_id, $request->title, $request->body);
                    break;
            }

            DB::commit();

            if ($response !== false) {
                return redirect()->back()->with('message', 'Notification sent successfully');
            } else {
                DB::rollBack();
                return redirect()->back()->with('error', 'Notification was not sent');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Notification send error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }
}
