<?php

namespace Innoboxrr\LaravelNotifications\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{

    public function register()
    {
        
        // $this->mergeConfigFrom(__DIR__ . '/../../config/innoboxrrlaravelnotifications.php', 'innoboxrrlaravelnotifications');

    }

    public function boot()
    {
        
        // $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        // $this->loadViewsFrom(__DIR__.'/../../resources/views', 'innoboxrrlaravelnotifications');

        if ($this->app->runningInConsole()) {
            
            // $this->publishes([__DIR__.'/../../resources/views' => resource_path('views/vendor/innoboxrrlaravelnotifications'),], 'views');

            // $this->publishes([__DIR__.'/../../config/innoboxrrlaravelnotifications.php' => config_path('innoboxrrlaravelnotifications.php')], 'config');

        }

    }
    
}