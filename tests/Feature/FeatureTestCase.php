<?php

namespace Innoboxrr\LaravelNotifications\Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Notifications\Console\NotificationTableCommand;
use Illuminate\Support\Facades\Schema;
use Innoboxrr\LaravelNotifications\Tests\Fixtures\TestNotification;
use Innoboxrr\LaravelNotifications\Tests\Fixtures\User;
use Innoboxrr\LaravelNotifications\Tests\TestCase;
use Laravel\Sanctum\SanctumServiceProvider;

/**
 * Una aplicacion como la que deja laravel-setup: Sanctum para la sesion de la
 * SPA, un usuario Notifiable y la tabla que publica `make:notifications-table`.
 *
 * Sobre CSRF: las rutas van en el grupo `web`, que verifica el token. Estos
 * tests no lo desactivan con withoutMiddleware: PreventRequestForgery ya se lo
 * salta solo cuando la aplicacion corre tests (APP_ENV=testing). El recorrido
 * real de la SPA, con la cookie XSRF-TOKEN y la cabecera X-XSRF-TOKEN, se
 * prueba aparte en CsrfFlowTest saliendo de ese modo.
 */
abstract class FeatureTestCase extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [...parent::getPackageProviders($app), SanctumServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        // El grupo `web` cifra las cookies: sin APP_KEY no responde nada.
        $app['config']->set('app.key', 'base64:' . base64_encode(str_repeat('k', 32)));
        $app['config']->set('session.driver', 'array');
        $app['config']->set('auth.providers.users.model', User::class);
    }

    protected function defineDatabaseMigrations(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        // La misma migracion que la aplicacion obtiene con
        // `make:notifications-table` o `notifications:install`.
        $stub = dirname((new \ReflectionClass(NotificationTableCommand::class))->getFileName()) . '/stubs/notifications.stub';

        (require $stub)->up();
    }

    protected function makeUser(string $name = 'Ana'): User
    {
        return User::create([
            'name' => $name,
            'email' => strtolower($name) . '@example.com',
            'password' => 'secret',
        ]);
    }

    protected function notify(User $user, string $message, ?string $action = null): string
    {
        $before = $user->notifications()->pluck('id');

        $user->notify(new TestNotification($message, $action));

        return $user->notifications()->whereNotIn('id', $before)->value('id');
    }
}
