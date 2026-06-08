<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $notifications = Notification::with('pengirim')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate($request->per_page ?? 20);

        return $this->success(NotificationResource::collection($notifications));
    }

    public function unread(Request $request): JsonResponse
    {
        $notifications = Notification::with('pengirim')
            ->where('user_id', $request->user()->id)
            ->where('is_read', false)
            ->latest()
            ->get();

        return $this->success([
            'total' => $notifications->count(),
            'data' => NotificationResource::collection($notifications),
        ]);
    }

    public function markAsRead(Request $request, Notification $notification): JsonResponse
    {
        if ($notification->user_id !== $request->user()->id) {
            return $this->forbidden();
        }

        $notification->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return $this->success(new NotificationResource($notification), 'Notifikasi ditandai sudah dibaca');
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        Notification::where('user_id', $request->user()->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return $this->success(null, 'Semua notifikasi ditandai sudah dibaca');
    }

    public function destroy(Notification $notification): JsonResponse
    {
        if ($notification->user_id !== request()->user()->id) {
            return $this->forbidden();
        }

        $notification->delete();

        return $this->success(null, 'Notifikasi berhasil dihapus');
    }
}
