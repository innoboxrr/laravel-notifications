<?php

namespace Innoboxrr\LaravelNotifications\Tests\Feature;

use Illuminate\Support\ServiceProvider;
use Innoboxrr\LaravelNotifications\Providers\AppServiceProvider;
use Innoboxrr\LaravelNotifications\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

final class ConfigurationTest extends TestCase
{
    #[Test]
    public function la_configuracion_del_paquete_esta_cargada_sin_publicarla(): void
    {
        $this->assertSame(['mail', 'database'], config('innoboxrrlaravelnotifications.notification_via'));
        $this->assertSame('App\Models\User', config('innoboxrrlaravelnotifications.user_class'));
    }

    #[Test]
    public function la_configuracion_se_puede_publicar_con_la_etiqueta_config(): void
    {
        $paths = ServiceProvider::pathsToPublish(AppServiceProvider::class, 'config');

        $this->assertSame(
            [realpath(dirname(__DIR__, 2) . '/config/innoboxrrlaravelnotifications.php')],
            array_map('realpath', array_keys($paths))
        );
        $this->assertSame([config_path('innoboxrrlaravelnotifications.php')], array_values($paths));
    }
}
