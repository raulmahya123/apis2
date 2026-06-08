<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;

class NotificationService
{
    public function send(User $user, string $type, string $title, string $message, ?User $pengirim = null, ?array $data = null): Notification
    {
        return Notification::create([
            'user_id' => $user->id,
            'pengirim_id' => $pengirim?->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
        ]);
    }

    public function sendToRole(string $role, string $type, string $title, string $message, ?User $pengirim = null, ?array $data = null): void
    {
        $users = User::where('role', $role)->get();
        foreach ($users as $user) {
            $this->send($user, $type, $title, $message, $pengirim, $data);
        }
    }

    public function sendToKaprodi(string $type, string $title, string $message, ?User $pengirim = null, ?array $data = null): void
    {
        $this->sendToRole('kaprodi', $type, $title, $message, $pengirim, $data);
    }

    public function sendToAdmin(string $type, string $title, string $message, ?User $pengirim = null, ?array $data = null): void
    {
        $this->sendToRole('admin_akademik', $type, $title, $message, $pengirim, $data);
    }
}
