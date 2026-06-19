<?php

namespace App\Services;

use App\Http\Controllers\Admin\FCMController;
use App\Models\Notification;
use App\Models\Provider;
use App\Models\User;

class NotificationService
{
    public function notifyUser(int|User $user, string $title, string $body, array $data = [], string $screen = 'notification', bool $store = true): bool
    {
        $userId = $user instanceof User ? $user->id : $user;

        if ($store) {
            $this->store([
                'title' => $title,
                'body' => $body,
                'type' => 3,
                'user_id' => $userId,
            ]);
        }

        return FCMController::sendMessageToUser($title, $body, $userId, $data, $screen);
    }

    public function notifyProvider(int|Provider $provider, string $title, string $body, array $data = [], string $screen = 'notification', bool $store = true): bool
    {
        $providerId = $provider instanceof Provider ? $provider->id : $provider;

        if ($store) {
            $this->store([
                'title' => $title,
                'body' => $body,
                'type' => 4,
                'provider_id' => $providerId,
            ]);
        }

        return FCMController::sendMessageToProvider($title, $body, $providerId, $data, $screen);
    }

    public function notifyAllUsers(string $title, string $body, array $data = [], string $screen = 'notification', bool $store = true): bool
    {
        if ($store) {
            $this->store([
                'title' => $title,
                'body' => $body,
                'type' => 1,
            ]);
        }

        return FCMController::sendMessageToAllUsers($title, $body, $data, $screen);
    }

    public function notifyAllProviders(string $title, string $body, array $data = [], string $screen = 'notification', bool $store = true): bool
    {
        if ($store) {
            $this->store([
                'title' => $title,
                'body' => $body,
                'type' => 2,
            ]);
        }

        return FCMController::sendMessageToAllProviders($title, $body, $data, $screen);
    }

    public function notifyAll(string $title, string $body, array $data = [], string $screen = 'notification', bool $store = true): bool
    {
        if ($store) {
            $this->store([
                'title' => $title,
                'body' => $body,
                'type' => 0,
            ]);
        }

        return FCMController::sendMessageToAll($title, $body, $data, $screen);
    }

    private function store(array $attributes): void
    {
        Notification::create($attributes);
    }
}
