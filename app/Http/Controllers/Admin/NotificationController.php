<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Notification;
use App\Models\Provider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

            // Determine who to send to based on type
            switch ($request->type) {
                case 0: // Send to ALL (users + providers)
                    $response = FCMController::sendMessageToAll($request->title, $request->body);

                    // Save notification for all users
                    $users = User::all();
                    foreach ($users as $user) {
                        Notification::create([
                            'title' => $request->title,
                            'body' => $request->body,
                            'type' => 1,
                            'user_id' => $user->id,
                        ]);
                    }

                    // Save notification for all providers
                    $providers = Provider::all();
                    foreach ($providers as $provider) {
                        Notification::create([
                            'title' => $request->title,
                            'body' => $request->body,
                            'type' => 2,
                            'provider_id' => $provider->id,
                        ]);
                    }
                    break;

                case 1: // Send to ALL Users
                    $users = User::all();
                    foreach ($users as $user) {
                        FCMController::sendMessageToUser($request->title, $request->body, $user->id);

                        Notification::create([
                            'title' => $request->title,
                            'body' => $request->body,
                            'type' => 1,
                            'user_id' => $user->id,
                        ]);
                    }
                    $response = true;
                    break;

                case 2: // Send to ALL Providers
                    $providers = Provider::all();
                    foreach ($providers as $provider) {
                        FCMController::sendMessageToProvider($request->title, $request->body, $provider->id);

                        Notification::create([
                            'title' => $request->title,
                            'body' => $request->body,
                            'type' => 2,
                            'provider_id' => $provider->id,
                        ]);
                    }
                    $response = true;
                    break;

                case 3: // Send to Specific User
                    $user = User::find($request->user_id);
                    if ($user) {
                        $response = FCMController::sendMessageToUser($request->title, $request->body, $user->id);

                        Notification::create([
                            'title' => $request->title,
                            'body' => $request->body,
                            'type' => 1,
                            'user_id' => $user->id,
                        ]);
                    }
                    break;

                case 4: // Send to Specific Provider
                    $provider = Provider::find($request->provider_id);
                    if ($provider) {
                        $response = FCMController::sendMessageToProvider($request->title, $request->body, $provider->id);

                        Notification::create([
                            'title' => $request->title,
                            'body' => $request->body,
                            'type' => 2,
                            'provider_id' => $provider->id,
                        ]);
                    }
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
