<?php

namespace Innoboxrr\LaravelNotifications\Providers;

use Illuminate\Support\Facades\Route;
use Innoboxrr\LaravelNotifications\Http\Controllers\NotificationController;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{

    public function map()
    {
        $this->mapWebRoutes();
    }

    protected function mapWebRoutes()
    {
        Route::middleware('web')
            ->get('notification/read/{notificationId}', [NotificationController::class, 'markNotificationAsRead'])
            ->name('read.noification');

        Route::middleware('web')
            ->prefix('innoboxrr/notifications')
            ->as('innoboxrr.notifications.')
            ->namespace('Innoboxrr\LaravelNotifications\Http\Controllers')
            ->group(__DIR__ . '/../../routes/web.php');
    }

}
