<?php

use Illuminate\Support\Facades\Route;

use Innoboxrr\LaravelNotifications\Http\Controllers\NotificationController;

Route::get('/users/{userId}/notifications', [NotificationController::class, 'getAllNotifications']);
Route::get('/users/{userId}/notifications/unread', [NotificationController::class, 'getUnreadNotifications']);
Route::post('/users/{userId}/notifications/markAsRead', [NotificationController::class, 'markAsRead']);
Route::delete('/users/{userId}/notifications', [NotificationController::class, 'deleteNotifications']);
Route::post('/users/{userId}/notifications/{notificationId}/markAsRead', [NotificationController::class, 'markNotificationAsRead']);
