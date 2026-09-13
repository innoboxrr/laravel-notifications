<?php

namespace Innoboxrr\LaravelNotifications\Providers;

use Illuminate\Contracts\Foundation\CachesRoutes;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Innoboxrr\LaravelNotifications\Http\Controllers\NotificationController;

/**
 * Hereda de ServiceProvider y no del RouteServiceProvider de Foundation. Aquel
 * guarda en una propiedad estatica de la clase base el cargador de rutas que
 * bootstrap/app.php registra con withRouting(), y cada subclase lo vuelve a
 * ejecutar: la aplicacion cargaba sus routes/web.php y api.php una vez mas por
 * cada paquete.
 */
class RouteServiceProvider extends ServiceProvider
{

    public function boot(): void
    {
        // Cuando las rutas estan cacheadas no hay que volver a registrarlas.
        if ($this->app instanceof CachesRoutes && $this->app->routesAreCached()) {
            return;
        }

        $this->mapWebRoutes();
    }

    protected function mapWebRoutes(): void
    {
        // El enlace de los correos. El nombre conserva la errata
        // (`read.noification`) porque las aplicaciones ya lo usan: un nombre
        // corregido no puede convivir con el, una ruta solo tiene uno.
        Route::middleware('web')
            ->get('notification/read/{notificationId}', [NotificationController::class, 'markNotificationAsRead'])
            ->name('read.noification');

        Route::middleware('web')
            ->prefix('innoboxrr/notifications')
            ->as('innoboxrr.notifications.')
            ->group(__DIR__ . '/../../routes/web.php');
    }

}
