<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\AppSetting;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Google\Client as GoogleClient;

class FCMController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public static function sendMessage($title, $body, $fcmToken, $userId, $screen = "order")
    {
        if (!$fcmToken) {
            \Log::error("FCM Error: No FCM token provided for user ID $userId");
            return false;
        }

        $credentialsFilePath = base_path('json/noor-9754e-4241cd69821b.json');

        try {
            $client = new GoogleClient();
            $client->setAuthConfig($credentialsFilePath);
            $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
            $client->useApplicationDefaultCredentials();
            $client->fetchAccessTokenWithAssertion();
            $tokenResponse = $client->getAccessToken();

            $access_token = $tokenResponse['access_token'];
            \Log::info("FCM Access Token for user ID $userId: " . $access_token);

            $headers = [
                "Authorization: Bearer $access_token",
                'Content-Type: application/json'
            ];

            $data = [
                "message" => [
                    "token" => $fcmToken,
                    "notification" => [
                        "title" => $title,
                        "body" => $body
                    ],
                    "data" => [
                        'screen' => $screen,
                        "click_action" => "FLUTTER_NOTIFICATION_CLICK"
                    ],
                    "android" => [
                        "priority" => "high"
                    ]
                ]
            ];

            $payload = json_encode($data);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/v1/projects/noor-9754e/messages:send');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_VERBOSE, true); // Enable verbose output for debugging
            $result = curl_exec($ch);
            $err = curl_error($ch);
            curl_close($ch);

            if ($result === false || $err) {
                \Log::error("FCM Error for user ID $userId: cURL Error: " . $err);
                return false;
            } else {
                $response = json_decode($result, true);
                \Log::info("FCM Response for user ID $userId: " . json_encode($response));
                if (isset($response['name'])) {
                    return true;
                } else {
                    \Log::error("FCM Error for user ID $userId: " . json_encode($response));
                    if (isset($response['error']['details'][0]['errorCode']) && $response['error']['details'][0]['errorCode'] === 'UNREGISTERED') {
                        \Log::info("FCM token cleanup for user ID $userId");
                        User::where('id', $userId)->update(['fcm_token' => null]);
                    }
                    return false;
                }
            }
        } catch (\Exception $e) {
            \Log::error("FCM Error for user ID $userId: " . $e->getMessage());
            return false;
        }
    }

   public static function sendMessageToAll($title, $body, $type = 0, $userId = null): bool
    {
        $query = User::query();
    
        // Filter based on type
        if ($type == 1) { // Regular Users
            $query->where('user_type', 1);
        } elseif ($type == 2) { // Parents
            $query->where('user_type', 3);
        } elseif ($type == 3) { // Teachers
            $query->where('user_type', 2);
        } elseif ($type == 4 && $userId) { // Specific User
            $query->where('id', $userId);
        }
    
        // Get the users based on the conditions
        $users = $query->whereNotNull('fcm_token')->get();
    
        if ($users->isEmpty()) {
            \Log::warning("No users found for FCM notification with type: $type");
            return false;
        }
    
        $allSent = true;
    
        foreach ($users as $user) {
            $sent = self::sendMessage($title, $body, $user->fcm_token, $user->id);
    
            if (!$sent) {
                $allSent = false;
                \Log::error("FCM notification failed for user ID " . $user->id);
            }
        }
    
        return $allSent;
    }

    
    public static function sendMessageToUser($title, $body, $user_id): bool
    {
        // Find the user by the provided user_id
        $user = User::find($user_id);
    
        // Check if user exists and has an FCM token
        if (!$user || is_null($user->fcm_token)) {
            \Log::error("User not found or has no FCM token for user ID " . $user_id);
            return false;
        }
    
        // Send the message using the FCM token
        $sent = self::sendMessage($title, $body, $user->fcm_token, $user->id);
    
        // Log an error if sending fails
        if (!$sent) {
            \Log::error("FCM notification failed for user ID " . $user->id);
        }
    
        return $sent;
    }

}
