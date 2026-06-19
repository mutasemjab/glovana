<?php

namespace App\Http\Controllers\Admin;

use App\Models\Provider;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Google\Client as GoogleClient;

class FCMController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public static function sendMessage($title, $body, $fcmToken, $notifiableId, string $notifiableType = 'user', $screen = 'notification', array $data = [])
    {
        if (!$fcmToken) {
            \Log::warning("FCM skipped: no token for {$notifiableType} ID {$notifiableId}");
            return false;
        }

        $credentialsFilePath = self::resolveCredentialsFilePath();

        if (!$credentialsFilePath) {
            \Log::error('FCM Error: Firebase credentials file could not be resolved.');
            return false;
        }

        try {
            $client = new GoogleClient();
            $client->setAuthConfig($credentialsFilePath);
            $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
            $client->fetchAccessTokenWithAssertion();
            $tokenResponse = $client->getAccessToken();
            $accessToken = $tokenResponse['access_token'] ?? null;

            if (!$accessToken) {
                \Log::error("FCM Error: access token missing for {$notifiableType} ID {$notifiableId}");
                return false;
            }

            $headers = [
                "Authorization: Bearer $accessToken",
                'Content-Type: application/json'
            ];

            if (!empty($data['screen']) && is_string($data['screen'])) {
                $screen = $data['screen'];
            }

            $payloadData = self::stringifyDataPayload(array_merge([
                'screen' => $screen,
                'key' => $screen,
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            ], $data));

            $payload = [
                'message' => [
                    'token' => $fcmToken,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                    'data' => $payloadData,
                    'android' => [
                        'priority' => 'high',
                        'notification' => [
                            'sound' => 'default',
                        ],
                    ],
                    'apns' => [
                        'headers' => [
                            'apns-priority' => '10',
                        ],
                        'payload' => [
                            'aps' => [
                                'sound' => 'default',
                                'content-available' => 1,
                            ],
                        ],
                    ],
                ],
            ];

            $payload = json_encode($payload);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/v1/projects/' . self::resolveProjectId($credentialsFilePath) . '/messages:send');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            $result = curl_exec($ch);
            $err = curl_error($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($result === false || $err) {
                \Log::error("FCM Error for {$notifiableType} ID {$notifiableId}: cURL Error: {$err}");
                return false;
            }

            $response = json_decode($result, true);

            if (isset($response['name'])) {
                return true;
            }

            \Log::error("FCM Error for {$notifiableType} ID {$notifiableId}", [
                'http_code' => $httpCode,
                'response' => $response ?? $result,
            ]);

            if (self::shouldCleanupToken($response)) {
                self::cleanupInvalidToken($notifiableType, $notifiableId);
            }

            return false;
        } catch (\Exception $e) {
            \Log::error("FCM Error for {$notifiableType} ID {$notifiableId}: " . $e->getMessage());
            return false;
        }
    }

    private static function stringifyDataPayload(array $data): array
    {
        $normalized = [];

        foreach ($data as $key => $value) {
            if ($value === null) {
                continue;
            }

            $normalized[$key] = is_scalar($value) ? (string) $value : json_encode($value);
        }

        return $normalized;
    }

    public static function sendMessageToAll($title, $body, array $data = [], string $screen = 'notification'): bool
    {
        $usersSent = self::sendMessageToAllUsers($title, $body, $data, $screen);
        $providersSent = self::sendMessageToAllProviders($title, $body, $data, $screen);

        return $usersSent || $providersSent;
    }

    public static function sendMessageToAllUsers($title, $body, array $data = [], string $screen = 'notification'): bool
    {
        $users = User::query()
            ->whereNotNull('fcm_token')
            ->where('fcm_token', '!=', '')
            ->get();

        if ($users->isEmpty()) {
            \Log::warning('No users available for broadcast notification.');
            return false;
        }

        $sentAny = false;

        foreach ($users as $user) {
            $sentAny = self::sendMessage($title, $body, $user->fcm_token, $user->id, 'user', $screen, $data) || $sentAny;
        }

        return $sentAny;
    }

    public static function sendMessageToAllProviders($title, $body, array $data = [], string $screen = 'notification'): bool
    {
        $providers = Provider::query()
            ->whereNotNull('fcm_token')
            ->where('fcm_token', '!=', '')
            ->get();

        if ($providers->isEmpty()) {
            \Log::warning('No providers available for broadcast notification.');
            return false;
        }

        $sentAny = false;

        foreach ($providers as $provider) {
            $sentAny = self::sendMessage($title, $body, $provider->fcm_token, $provider->id, 'provider', $screen, $data) || $sentAny;
        }

        return $sentAny;
    }

    public static function sendMessageToUser($title, $body, $user_id, array $data = [], $screen = 'notification'): bool
    {
        $user = User::find($user_id);

        if (!$user || blank($user->fcm_token)) {
            \Log::warning("User not found or has no FCM token for user ID {$user_id}");
            return false;
        }

        $sent = self::sendMessage($title, $body, $user->fcm_token, $user->id, 'user', $screen, $data);

        if (!$sent) {
            \Log::error("FCM notification failed for user ID {$user->id}");
        }

        return $sent;
    }

    public static function sendMessageToProvider($title, $body, $provider_id, array $data = [], $screen = 'notification'): bool
    {
        $provider = Provider::find($provider_id);

        if (!$provider || blank($provider->fcm_token)) {
            \Log::warning("Provider not found or has no FCM token for provider ID {$provider_id}");
            return false;
        }

        $sent = self::sendMessage($title, $body, $provider->fcm_token, $provider->id, 'provider', $screen, $data);

        if (!$sent) {
            \Log::error("FCM notification failed for provider ID {$provider->id}");
        }

        return $sent;
    }

    private static function resolveCredentialsFilePath(): ?string
    {
        $configuredPath = config('firebase.projects.' . config('firebase.default') . '.credentials')
            ?: env('GOOGLE_APPLICATION_CREDENTIALS');

        if (is_string($configuredPath) && $configuredPath !== '') {
            if (file_exists($configuredPath)) {
                return $configuredPath;
            }

            $relativePath = base_path($configuredPath);
            if (file_exists($relativePath)) {
                return $relativePath;
            }
        }

        $legacyPath = base_path('json/glovana-4f28a-6479357d119a.json');

        return file_exists($legacyPath) ? $legacyPath : null;
    }

    private static function resolveProjectId(?string $credentialsFilePath): string
    {
        $projectId = env('FIREBASE_PROJECT_ID');

        if (is_string($projectId) && $projectId !== '') {
            return $projectId;
        }

        if ($credentialsFilePath && file_exists($credentialsFilePath)) {
            $decoded = json_decode((string) file_get_contents($credentialsFilePath), true);

            if (!empty($decoded['project_id'])) {
                return $decoded['project_id'];
            }
        }

        return 'glovana-4f28a';
    }

    private static function shouldCleanupToken(?array $response): bool
    {
        return isset($response['error']['details'][0]['errorCode'])
            && $response['error']['details'][0]['errorCode'] === 'UNREGISTERED';
    }

    private static function cleanupInvalidToken(string $notifiableType, int $notifiableId): void
    {
        if ($notifiableType === 'provider') {
            Provider::where('id', $notifiableId)->update(['fcm_token' => null]);
            return;
        }

        User::where('id', $notifiableId)->update(['fcm_token' => null]);
    }
}
