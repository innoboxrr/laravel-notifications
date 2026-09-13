# Laravel Notifications

`innoboxrr/laravel-notifications` expone por HTTP las notificaciones de base de datos de Laravel del usuario autenticado: listarlas, contarlas, marcarlas como leídas y borrarlas. Es lo que usa el desplegable de notificaciones de la interfaz de administración que instala `innoboxrr/laravel-setup`.

El paquete no define modelos ni notificaciones propias: trabaja con la relación `notifications` del trait `Illuminate\Notifications\Notifiable`.

## Requisitos

- PHP 8.3 o superior.
- Laravel 13.
- Laravel Sanctum (se instala como dependencia). Las rutas exigen el guard `auth:sanctum`.
- Un modelo de usuario con el trait `Notifiable` (el `App\Models\User` de Laravel ya lo trae).
- La tabla `notifications` (ver abajo).

## Instalación

```bash
composer require innoboxrr/laravel-notifications
```

Los proveedores se registran solos por package discovery.

### La tabla `notifications`

Una aplicación nueva de Laravel no trae esta tabla. Créala con:

```bash
php artisan notifications:install
php artisan migrate
```

`notifications:install` publica la migración de Laravel (`make:notifications-table`) solo si la aplicación todavía no tiene una `*_create_notifications_table.php` ni la tabla existe. Se puede correr las veces que haga falta, por ejemplo desde un instalador. Si prefieres el comando de Laravel, `php artisan make:notifications-table` hace lo mismo, pero falla si la migración ya existe.

## Configuración

La configuración se carga sin publicarla. Para copiarla a `config/innoboxrrlaravelnotifications.php`:

```bash
php artisan vendor:publish --provider="Innoboxrr\LaravelNotifications\Providers\AppServiceProvider" --tag=config
```

| Clave | Valor por defecto |
|---|---|
| `user_class` | `App\Models\User` |
| `notification_via` | `['mail', 'database']` |
| `excel_view` | `innoboxrrlaravelnotifications::excel.` |
| `export_disk` | `s3` |

Las rutas de este paquete no leen ninguna de estas claves hoy: siempre usan el usuario autenticado. Se conservan para las aplicaciones que las consultan.

## Autenticación y CSRF

Las rutas están en el grupo `web` y detrás de `auth:sanctum`, pensadas para una SPA en el mismo dominio que usa la sesión por cookie:

1. La SPA pide `GET /sanctum/csrf-cookie` (o cualquier página del grupo `web`) y recibe la cookie `XSRF-TOKEN`.
2. axios la devuelve sola en la cabecera `X-XSRF-TOKEN` en cada `POST` o `DELETE`.
3. Sin esa cabecera, una petición que escribe recibe `419`.

Un invitado que pide JSON (`Accept: application/json`) recibe `401` con `{"message": "Unauthenticated."}`. Sin JSON, Laravel lo redirige al login.

## Endpoints

Las rutas de la API cuelgan de `/innoboxrr/notifications` y sus nombres de `innoboxrr.notifications.`. Todas operan solo sobre las notificaciones del usuario autenticado.

| Método | URI | Nombre | Respuesta |
|---|---|---|---|
| GET | `/innoboxrr/notifications/notifications` | `innoboxrr.notifications.index` | Arreglo de notificaciones, las más recientes primero |
| GET | `/innoboxrr/notifications/notifications/unread` | `innoboxrr.notifications.index.unread` | Arreglo de notificaciones no leídas |
| GET | `/innoboxrr/notifications/notifications/unread/count` | `innoboxrr.notifications.index.unread.count` | `{"count": 3}` |
| POST | `/innoboxrr/notifications/notifications/markAsRead` | `innoboxrr.notifications.mark.all.as.read` | `{"success": true, "count": 3}` |
| DELETE | `/innoboxrr/notifications/notifications` | `innoboxrr.notifications.delete.all` | `{"success": true, "count": 5}` |
| POST | `/innoboxrr/notifications/notifications/{id}/markAsRead` | `innoboxrr.notifications.mark.as.read` | `{"success": true, "id": "…", "action": "admin/orders/7", "read_at": "…"}` |
| GET | `/notification/read/{id}` | `read.noification` | Redirección a `data.action`, o a `/` si no tiene |

### Listar con `limit`

`index` e `index.unread` aceptan `?limit=N` (entero mayor que 0) y devuelven las N más recientes. Sin `limit` devuelven todas. La respuesta es siempre un arreglo, con o sin `limit`. Un `limit` inválido responde `422`.

Para el indicador de la barra superior usa `index.unread.count`, que cuenta sin traer las notificaciones.

Cada notificación es la fila de la tabla tal cual:

```json
{
    "id": "9d1c…",
    "type": "App\\Notifications\\OrderCreated",
    "notifiable_type": "App\\Models\\User",
    "notifiable_id": 1,
    "data": { "message": "Pedido nuevo", "action": "admin/orders/7" },
    "read_at": null,
    "created_at": "2026-09-13T10:00:00.000000Z",
    "updated_at": "2026-09-13T10:00:00.000000Z"
}
```

### Marcar una como leída

La misma acción sirve a dos clientes:

- **La SPA** llama a `innoboxrr.notifications.mark.as.read` pidiendo JSON y recibe `success`, `id`, `action` (`data.action` o `null`) y `read_at`. Si la notificación no existe o no es del usuario, recibe `404` con `{"success": false, "message": "Notification not found"}`.
- **El enlace de un correo** apunta a `read.noification`. Es una navegación normal: marca la notificación y redirige a `data.action`, o a `/` si no tiene. Si esa ruta se pide con JSON, responde igual que la anterior.

`mark.all.as.read` y `delete.all` responden JSON cuando la petición lo pide, y texto plano (`Notifications marked as read`, `Notifications deleted`) cuando no.

### El nombre `read.noification`

La errata es histórica y se conserva porque las aplicaciones ya generan enlaces con ese nombre. Laravel no permite dos nombres para una misma ruta, así que no hay un alias bien escrito: usa `read.noification`.

## Enviar notificaciones

Cualquier notificación con el canal `database` aparece en estos endpoints. El desplegable de la interfaz de administración lee estas claves de `data`:

```php
public function via(object $notifiable): array
{
    return ['mail', 'database'];
}

public function toArray(object $notifiable): array
{
    return [
        'message' => 'Pedido nuevo',
        'action' => 'admin/orders/7', // a donde lleva al abrirla
        'img' => $this->sender->avatar_url,
        'from_name' => $this->sender->name,
    ];
}
```

## Pruebas

```bash
composer install
vendor/bin/phpunit
```

La suite monta una aplicación de Testbench con Sanctum, un usuario `Notifiable` y la migración de Laravel. Cubre cada endpoint, el recorrido CSRF de la SPA, `notifications:install` y el arranque con `CACHE_STORE=database` sin tabla de caché.

## Licencia

MIT.
