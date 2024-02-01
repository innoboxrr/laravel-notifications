<?php

use Illuminate\Support\Facades\Route;

use Innoboxrr\LaravelNotifications\Http\Controllers\NotificationController;

Route::get('notifications', [NotificationController::class, 'getAllNotifications']);
Route::get('notifications/unread', [NotificationController::class, 'getUnreadNotifications']);
Route::post('notifications/markAsRead', [NotificationController::class, 'markAsRead']);
Route::delete('notifications', [NotificationController::class, 'deleteNotifications']);
Route::post('notifications/{notificationId}/markAsRead', [NotificationController::class, 'markNotificationAsRead']);
