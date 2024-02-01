<?php

namespace Innoboxrr\LaravelNotifications\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Redirect;

class NotificationController extends Controller
{
    public function getAllNotifications($userId)
    {
        $user = User::findOrFail($userId);
        return response()->json($user->notifications);
    }

    public function getUnreadNotifications($userId)
    {
        $user = User::findOrFail($userId);
        return response()->json($user->unreadNotifications);
    }

    public function markAsRead($userId)
    {
        $user = User::findOrFail($userId);
        $user->unreadNotifications->markAsRead();
        return response('Notifications marked as read');
    }

    public function deleteNotifications($userId)
    {
        $user = User::findOrFail($userId);
        $user->notifications()->delete();
        return response('Notifications deleted');
    }

    public function markNotificationAsRead($userId, $notificationId)
    {
        $user = User::findOrFail($userId);
        $notification = $user->notifications()->where('id', $notificationId)->first();

        if ($notification) {
            $notification->markAsRead();
            $actionUrl = isset($notification->data['action']) ? $notification->data['action'] : '/';
            return Redirect::to($actionUrl);
        }

        return response('Notification not found', 404);
    }
}
