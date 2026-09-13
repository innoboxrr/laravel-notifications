<?php

namespace Innoboxrr\LaravelNotifications\Tests\Feature;

use PHPUnit\Framework\Attributes\Test;

final class NotificationEndpointsTest extends FeatureTestCase
{
    #[Test]
    public function un_invitado_recibe_401_en_json_en_cada_ruta(): void
    {
        $endpoints = [
            ['GET', route('innoboxrr.notifications.index')],
            ['GET', route('innoboxrr.notifications.index.unread')],
            ['POST', route('innoboxrr.notifications.mark.all.as.read')],
            ['DELETE', route('innoboxrr.notifications.delete.all')],
            ['POST', route('innoboxrr.notifications.mark.as.read', 'any-id')],
            ['GET', route('read.noification', 'any-id')],
        ];

        foreach ($endpoints as [$method, $uri]) {
            $this->json($method, $uri)
                ->assertStatus(401)
                ->assertExactJson(['message' => 'Unauthenticated.']);
        }
    }

    #[Test]
    public function lista_solo_las_notificaciones_del_usuario(): void
    {
        $ana = $this->makeUser('Ana');
        $beto = $this->makeUser('Beto');

        $first = $this->notify($ana, 'Primera');
        $second = $this->notify($ana, 'Segunda');
        $this->notify($beto, 'De Beto');

        $response = $this->actingAs($ana)
            ->getJson(route('innoboxrr.notifications.index'))
            ->assertOk()
            ->assertJsonCount(2);

        $this->assertEqualsCanonicalizing([$first, $second], array_column($response->json(), 'id'));
        $this->assertNotContains('De Beto', array_column(array_column($response->json(), 'data'), 'message'));
    }

    #[Test]
    public function lista_solo_las_no_leidas_del_usuario(): void
    {
        $ana = $this->makeUser('Ana');
        $beto = $this->makeUser('Beto');

        $read = $this->notify($ana, 'Leida');
        $unread = $this->notify($ana, 'Pendiente');
        $this->notify($beto, 'De Beto');

        $ana->notifications()->whereKey($read)->first()->markAsRead();

        $this->actingAs($ana)
            ->getJson(route('innoboxrr.notifications.index.unread'))
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', $unread)
            ->assertJsonPath('0.read_at', null);
    }

    #[Test]
    public function marcar_una_como_leida_por_xhr_responde_json(): void
    {
        $ana = $this->makeUser('Ana');
        $id = $this->notify($ana, 'Pedido nuevo', 'admin/orders/7');
        $other = $this->notify($ana, 'Otra');

        $this->actingAs($ana)
            ->postJson(route('innoboxrr.notifications.mark.as.read', $id))
            ->assertOk()
            ->assertJson(['success' => true, 'id' => $id, 'action' => 'admin/orders/7'])
            ->assertJsonStructure(['read_at']);

        $this->assertNotNull($ana->notifications()->find($id)->read_at);
        $this->assertNull($ana->notifications()->find($other)->read_at);
    }

    #[Test]
    public function marcar_una_sin_accion_devuelve_action_null(): void
    {
        $ana = $this->makeUser('Ana');
        $id = $this->notify($ana, 'Sin enlace');

        $this->actingAs($ana)
            ->postJson(route('innoboxrr.notifications.mark.as.read', $id))
            ->assertOk()
            ->assertJsonPath('action', null);
    }

    #[Test]
    public function no_se_puede_marcar_la_notificacion_de_otro_usuario(): void
    {
        $ana = $this->makeUser('Ana');
        $beto = $this->makeUser('Beto');
        $id = $this->notify($beto, 'De Beto');

        $this->actingAs($ana)
            ->postJson(route('innoboxrr.notifications.mark.as.read', $id))
            ->assertNotFound()
            ->assertJson(['success' => false]);

        $this->assertNull($beto->notifications()->find($id)->read_at);
    }

    #[Test]
    public function el_enlace_del_correo_sigue_redirigiendo_a_la_accion(): void
    {
        $ana = $this->makeUser('Ana');
        $id = $this->notify($ana, 'Pedido nuevo', 'admin/orders/7');

        $this->actingAs($ana)
            ->get(route('read.noification', $id))
            ->assertRedirect(url('admin/orders/7'));

        $this->assertNotNull($ana->notifications()->find($id)->read_at);
    }

    #[Test]
    public function marcar_todas_como_leidas_responde_json_y_no_toca_a_otros(): void
    {
        $ana = $this->makeUser('Ana');
        $beto = $this->makeUser('Beto');
        $this->notify($ana, 'Una');
        $this->notify($ana, 'Dos');
        $this->notify($beto, 'De Beto');

        $this->actingAs($ana)
            ->postJson(route('innoboxrr.notifications.mark.all.as.read'))
            ->assertOk()
            ->assertExactJson(['success' => true, 'count' => 2]);

        $this->assertSame(0, $ana->unreadNotifications()->count());
        $this->assertSame(1, $beto->unreadNotifications()->count());
    }

    #[Test]
    public function borrar_todas_responde_json_y_no_toca_a_otros(): void
    {
        $ana = $this->makeUser('Ana');
        $beto = $this->makeUser('Beto');
        $this->notify($ana, 'Una');
        $this->notify($ana, 'Dos');
        $this->notify($beto, 'De Beto');

        $this->actingAs($ana)
            ->deleteJson(route('innoboxrr.notifications.delete.all'))
            ->assertOk()
            ->assertExactJson(['success' => true, 'count' => 2]);

        $this->assertSame(0, $ana->notifications()->count());
        $this->assertSame(1, $beto->notifications()->count());
    }

    #[Test]
    public function sin_json_las_masivas_conservan_su_respuesta_de_texto(): void
    {
        $ana = $this->makeUser('Ana');
        $this->notify($ana, 'Una');

        $this->actingAs($ana)
            ->post(route('innoboxrr.notifications.mark.all.as.read'))
            ->assertOk()
            ->assertSeeText('Notifications marked as read');

        $this->actingAs($ana)
            ->delete(route('innoboxrr.notifications.delete.all'))
            ->assertOk()
            ->assertSeeText('Notifications deleted');
    }
}
