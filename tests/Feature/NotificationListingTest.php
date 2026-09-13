<?php

namespace Innoboxrr\LaravelNotifications\Tests\Feature;

use PHPUnit\Framework\Attributes\Test;

final class NotificationListingTest extends FeatureTestCase
{
    #[Test]
    public function limit_trae_las_mas_recientes_y_la_respuesta_sigue_siendo_un_arreglo(): void
    {
        $ana = $this->makeUser('Ana');

        $this->notify($ana, 'Primera');
        $this->travel(1)->minutes();
        $second = $this->notify($ana, 'Segunda');
        $this->travel(1)->minutes();
        $third = $this->notify($ana, 'Tercera');

        $response = $this->actingAs($ana)
            ->getJson(route('innoboxrr.notifications.index', ['limit' => 2]))
            ->assertOk()
            ->assertJsonIsArray()
            ->assertJsonCount(2);

        $this->assertSame([$third, $second], array_column($response->json(), 'id'));
    }

    #[Test]
    public function sin_limit_devuelve_todas(): void
    {
        $ana = $this->makeUser('Ana');

        foreach (range(1, 3) as $n) {
            $this->notify($ana, "Numero {$n}");
        }

        $this->actingAs($ana)
            ->getJson(route('innoboxrr.notifications.index'))
            ->assertOk()
            ->assertJsonIsArray()
            ->assertJsonCount(3);
    }

    #[Test]
    public function limit_tambien_aplica_a_las_no_leidas(): void
    {
        $ana = $this->makeUser('Ana');

        $read = $this->notify($ana, 'Leida');
        $this->travel(1)->minutes();
        $this->notify($ana, 'Pendiente vieja');
        $this->travel(1)->minutes();
        $newest = $this->notify($ana, 'Pendiente nueva');

        $ana->notifications()->whereKey($read)->first()->markAsRead();

        $this->actingAs($ana)
            ->getJson(route('innoboxrr.notifications.index.unread', ['limit' => 1]))
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', $newest);
    }

    #[Test]
    public function un_limit_invalido_responde_422(): void
    {
        $ana = $this->makeUser('Ana');

        foreach (['0', '-3', 'abc'] as $limit) {
            $this->actingAs($ana)
                ->getJson(route('innoboxrr.notifications.index', ['limit' => $limit]))
                ->assertStatus(422)
                ->assertJsonValidationErrors('limit');
        }
    }

    #[Test]
    public function el_contador_de_no_leidas_cuenta_solo_las_del_usuario(): void
    {
        $ana = $this->makeUser('Ana');
        $beto = $this->makeUser('Beto');

        $read = $this->notify($ana, 'Leida');
        $this->notify($ana, 'Pendiente');
        $this->notify($ana, 'Otra pendiente');
        $this->notify($beto, 'De Beto');

        $ana->notifications()->whereKey($read)->first()->markAsRead();

        $this->actingAs($ana)
            ->getJson(route('innoboxrr.notifications.index.unread.count'))
            ->assertOk()
            ->assertExactJson(['count' => 2]);
    }

    #[Test]
    public function un_invitado_no_puede_contar(): void
    {
        $this->getJson(route('innoboxrr.notifications.index.unread.count'))
            ->assertStatus(401);
    }
}
