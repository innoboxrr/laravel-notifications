<?php

namespace Innoboxrr\LaravelNotifications\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{

    public function register(): void
    {

        $this->mergeConfigFrom(__DIR__ . '/../../config/innoboxrrlaravelnotifications.php', 'innoboxrrlaravelnotifications');

    }

    public function boot(): void
    {

        if ($this->app->runningInConsole()) {

            $this->publishes([__DIR__ . '/../../config/innoboxrrlaravelnotifications.php' => config_path('innoboxrrlaravelnotifications.php')], 'config');

        }

    }

}
