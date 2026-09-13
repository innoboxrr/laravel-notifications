<?php

use Illuminate\Support\Facades\Route;

use Innoboxrr\LaravelNotifications\Http\Controllers\NotificationController;

Route::get('notifications', [NotificationController::class, 'getAllNotifications'])
    ->name('index');

Route::get('notifications/unread', [NotificationController::class, 'getUnreadNotifications'])
    ->name('index.unread');

Route::get('notifications/unread/count', [NotificationController::class, 'countUnreadNotifications'])
    ->name('index.unread.count');

Route::post('notifications/markAsRead', [NotificationController::class, 'markAsRead'])
    ->name('mark.all.as.read');

Route::delete('notifications', [NotificationController::class, 'deleteNotifications'])
    ->name('delete.all');

Route::post('notifications/{notificationId}/markAsRead', [NotificationController::class, 'markNotificationAsRead'])
    ->name('mark.as.read');
