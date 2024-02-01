<?php

namespace Innoboxrr\LaravelNotifications\Http\Controllers;

use Illuminate\Support\Facades\Redirect;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function getAllNotifications()
    {
        $user = auth()->user();
        return response()->json($user->notifications);
    }

    public function getUnreadNotifications()
    {
        $user = auth()->user();
        return response()->json($user->unreadNotifications);
    }

    public function markAsRead()
    {
        $user = auth()->user();
        $user->unreadNotifications->markAsRead();
        return response('Notifications marked as read');
    }

    public function deleteNotifications()
    {
        $user = auth()->user();
        $user->notifications()->delete();
        return response('Notifications deleted');
    }

    public function markNotificationAsRead($notificationId)
    {
        $user = auth()->user();
        $notification = $user->notifications()->where('id', $notificationId)->first();

        if ($notification) {
            $notification->markAsRead();
            $actionUrl = isset($notification->data['action']) ? $notification->data['action'] : '/';
            return Redirect::to($actionUrl);
        }

        return response('Notification not found', 404);
    }
}
