<?php

namespace Innoboxrr\LaravelNotifications\Tests\Feature;

use PHPUnit\Framework\Attributes\Test;

/**
 * Las rutas del paquete van en el grupo `web`, asi que una peticion que escribe
 * necesita el token CSRF. Es el recorrido de la SPA con axios: la primera
 * respuesta deja la cookie XSRF-TOKEN y axios la devuelve en X-XSRF-TOKEN.
 */
final class CsrfFlowTest extends FeatureTestCase
{
    #[Test]
    public function la_spa_escribe_con_la_cabecera_xsrf_y_sin_ella_recibe_419(): void
    {
        $ana = $this->makeUser('Ana');
        $this->notify($ana, 'Una');

        $this->actingAs($ana);

        // PreventRequestForgery se salta la verificacion mientras la aplicacion
        // corre tests. Fuera de ese modo la verifica de verdad.
        $this->app['env'] = 'local';

        $this->postJson(route('innoboxrr.notifications.mark.all.as.read'))
            ->assertStatus(419);

        $this->assertSame(1, $ana->unreadNotifications()->count());

        $xsrf = $this->getJson(route('innoboxrr.notifications.index'))
            ->assertOk()
            ->getCookie('XSRF-TOKEN', decrypt: false);

        $this->assertNotNull($xsrf, 'La respuesta no dejo la cookie XSRF-TOKEN.');

        $this->withHeader('X-XSRF-TOKEN', $xsrf->getValue())
            ->postJson(route('innoboxrr.notifications.mark.all.as.read'))
            ->assertOk()
            ->assertJson(['success' => true, 'count' => 1]);
    }
}
