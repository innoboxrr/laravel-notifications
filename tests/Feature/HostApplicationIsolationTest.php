<?php

namespace Innoboxrr\LaravelNotifications\Tests\Feature;

use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as FoundationRouteServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Innoboxrr\LaravelNotifications\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * Lo que el paquete no debe hacerle a la aplicacion que lo instala.
 */
final class HostApplicationIsolationTest extends TestCase
{
    public static int $appRouteLoads = 0;

    protected function defineEnvironment($app): void
    {
        // Es lo que hace bootstrap/app.php con withRouting(): deja el cargador
        // de rutas de la aplicacion en la clase base de Foundation.
        self::$appRouteLoads = 0;

        FoundationRouteServiceProvider::loadRoutesUsing(function () {
            self::$appRouteLoads++;
        });
    }

    protected function tearDown(): void
    {
        FoundationRouteServiceProvider::loadRoutesUsing(null);

        parent::tearDown();
    }

    #[Test]
    public function no_vuelve_a_cargar_las_rutas_de_la_aplicacion(): void
    {
        $this->assertSame(0, self::$appRouteLoads);
    }

    #[Test]
    public function registra_sus_propias_rutas(): void
    {
        $this->assertTrue(Route::has('read.noification'));
        $this->assertTrue(Route::has('innoboxrr.notifications.index'));
        $this->assertTrue(Route::has('innoboxrr.notifications.mark.as.read'));
    }

    #[Test]
    public function no_agrega_otro_envio_del_correo_de_verificacion(): void
    {
        $this->assertArrayNotHasKey(Registered::class, Event::getRawListeners());
    }
}
