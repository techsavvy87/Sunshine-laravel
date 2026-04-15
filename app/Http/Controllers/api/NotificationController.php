<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function allNotifications(Request $request)
    {
        $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'page' => 'nullable|integer|min:1',
            'limit' => 'nullable|integer|min:1|max:100',
            'search' => 'nullable|string',
        ]);

        $userId = (int) $request->input('user_id');

        return response()->json(
            $this->buildNotificationsResponse(
                $userId,
                (int) $request->input('page', 1),
                (int) $request->input('limit', 5),
                $request->input('search')
            ),
            200
        );
    }

    public function pollNotifications(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $notifications = Notification::with(['sender.profile'])
            ->where('user_id', $request->user_id)
            ->where('is_read', false)
            ->latest()
            ->get()
            ->map(function ($notification) {
                $senderName = 'Admin';
                $senderImage = '';

                if (!empty($notification->sender?->profile?->first_name) || !empty($notification->sender?->profile?->last_name)) {
                    $senderName = trim(($notification->sender->profile->first_name ?? '') . ' ' . ($notification->sender->profile->last_name ?? ''));
                } elseif (!empty($notification->sender?->name)) {
                    $senderName = $notification->sender->name;
                }

                if (!empty($notification->sender?->profile?->avatar_img)) {
                    $senderImage = asset('storage/profiles/' . $notification->sender->profile->avatar_img);
                }

                return [
                    'id' => $notification->id,
                    'sender' => $senderName,
                    'sender_image' => $senderImage,
                    'message' => $notification->message,
                    'created_at' => $notification->created_at?->format('Y-m-d H:i:s'),
                ];
            })
            ->values();

        return response()->json([
            'notifications' => $notifications,
        ], 200);
    }

    public function markAsRead(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:notifications,id',
        ]);

        Notification::where('id', $request->id)
            ->update(['is_read' => true]);

        return response()->json([
            'status' => true,
            'message' => 'Notification marked as read.',
        ], 200);
    }

    public function markAllAsRead(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'required|exists:notifications,id',
        ]);

        Notification::whereIn('id', $request->ids)
            ->update(['is_read' => true]);

        return response()->json([
            'status' => true,
            'message' => 'Notifications marked as read.',
        ], 200);
    }

    public function toggleStatus(Request $request)
    {
        $request->validate([
            'notification_id' => 'required|exists:notifications,id',
            'user_id' => 'nullable|exists:users,id',
            'page' => 'nullable|integer|min:1',
            'limit' => 'nullable|integer|min:1|max:100',
            'search' => 'nullable|string',
        ]);

        $userId = (int) $request->input('user_id');
        $notification = Notification::where('id', $request->notification_id)
            ->where('user_id', $userId)
            ->firstOrFail();

        $notification->is_read = !$notification->is_read;
        $notification->save();

        return response()->json([
            'status' => true,
            'message' => 'Notification status updated successfully.',
        ] + $this->buildNotificationsResponse(
            $userId,
            (int) $request->input('page', 1),
            (int) $request->input('limit', 10),
            $request->input('search')
        ), 200);
    }

    public function delete(Request $request)
    {
        $request->validate([
            'notification_id' => 'required|exists:notifications,id',
        ]);

        $deleted = Notification::where('id', $request->notification_id)
            ->delete();

        if (!$deleted) {
            return response()->json([
                'status' => false,
                'message' => 'Notification not found.',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Notification deleted successfully.',
        ], 200);
    }

    private function buildNotificationsResponse(int $userId, int $page, int $limit, ?string $search = null): array
    {
        $page = max($page, 1);
        $limit = max(min($limit, 100), 1);

        $query = Notification::with(['sender.profile'])
            ->where('user_id', $userId);

        if (!empty($search)) {
            $query->where(function ($builder) use ($search) {
                $builder->where('title', 'like', '%' . $search . '%')
                    ->orWhere('message', 'like', '%' . $search . '%');
            });
        }

        $total = (clone $query)->count();

        $notifications = $query->latest()
            ->forPage($page, $limit)
            ->get()
            ->map(fn ($notification) => $this->formatNotification($notification))
            ->values();

        return [
            'notifications' => $notifications,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
            ],
        ];
    }

    private function formatNotification(Notification $notification): array
    {
        $senderName = 'Admin';
        $senderImage = '';

        if (!empty($notification->sender?->profile?->first_name) || !empty($notification->sender?->profile?->last_name)) {
            $senderName = trim(($notification->sender->profile->first_name ?? '') . ' ' . ($notification->sender->profile->last_name ?? ''));
        } elseif (!empty($notification->sender?->name)) {
            $senderName = $notification->sender->name;
        }

        if (!empty($notification->sender?->profile?->avatar_img)) {
            $senderImage = asset('storage/profiles/' . $notification->sender->profile->avatar_img);
        }

        return [
            'id' => $notification->id,
            'title' => $notification->title,
            'sender' => $senderName,
            'sender_image' => $senderImage,
            'status' => $notification->is_read ? 'read' : 'unread',
            'created_at' => $notification->created_at?->format('Y-m-d H:i:s'),
            'message' => $notification->message,
        ];
    }
}
