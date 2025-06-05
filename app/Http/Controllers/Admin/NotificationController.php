<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    public function create()
    {
        $users=User::get();
       return view('admin.notifications.create',compact('users'));

    }

    public function send(Request $request)
    {
        // Validate the input
        $this->validate($request, [
            'title' => 'required',
            'body' => 'required',
            'type' => 'required|in:0,1,2,3,4', // Ensure valid type
            'user_id' => 'nullable|required_if:type,4|exists:users,id', // If type = 4, user_id must be valid
        ]);
    
        // Send notification via Firebase Cloud Messaging (FCM)
        $response = FCMController::sendMessageToAll($request->title, $request->body);
    
        // Save the notification in the database
        $noti = new Notification([
            'title' => $request->title,
            'body' => $request->body,
            'type' => $request->type,
            'user_id' => $request->type == 4 ? $request->user_id : null, // Only store user_id if type = 4
        ]);
    
        $noti->save();
    
        if ($response) {
            return redirect()->back()->with('message', 'Notification sent successfully');
        } else {
            return redirect()->back()->with('error', 'Notification was not sent');
        }
    }

}
