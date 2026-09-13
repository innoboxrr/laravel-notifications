# Changelog

Todas las modificaciones notables a este proyecto serán documentadas en este archivo.

El formato se basa en [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
y este proyecto sigue [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [2.1.0] - 2026-09-13

Lo que necesita una aplicación nueva de Laravel 13 para usar las notificaciones
desde la SPA de administración.

### Añadido

- `limit` opcional en `innoboxrr.notifications.index` e `index.unread`: trae las N
  más recientes. Sin él se devuelven todas y la respuesta sigue siendo un arreglo.
- Ruta `innoboxrr.notifications.index.unread.count`
  (`GET /innoboxrr/notifications/notifications/unread/count`), que devuelve
  `{"count": N}`.
- Comando `notifications:install`: publica la migración de la tabla
  `notifications` solo si falta. Se puede correr varias veces.
- La configuración se puede publicar con `--tag=config`.
- README con la instalación, la autenticación con Sanctum, el CSRF y cada endpoint.

### Cambiado

- Las respuestas cuando la petición espera JSON:
  - Marcar una como leída responde `{"success", "id", "action", "read_at"}` en lugar
    de redirigir, y `404` en JSON si no es del usuario.
  - Marcar todas y borrar todas responden `{"success": true, "count": N}`.
  - Sin JSON, el enlace del correo sigue redirigiendo a `data.action` y las masivas
    siguen respondiendo texto.
- `composer.json` requiere `laravel/framework ^13.0` y `laravel/sanctum ^4.3` en lugar
  de `illuminate/support`. El paquete ya usaba Foundation y el guard `auth:sanctum`.
- `RouteServiceProvider`, `EventServiceProvider` y `AuthServiceProvider` heredan de
  `Illuminate\Support\ServiceProvider`. Las URIs, los nombres de ruta y las claves de
  configuración no cambian. Una aplicación que extendía estos proveedores debe revisar
  sus métodos: se quitaron `map()`, `mapPolicies()` y la detección de observers por
  carpeta, que no encontraban nada en este paquete.
- Marcar todas como leídas es un solo `UPDATE`.

### Corregido

- `php artisan migrate` fallaba en una aplicación nueva con `CACHE_STORE=database`:
  el arranque leía la caché antes de que existiera la tabla `cache`. Además, las
  claves `auth_policies` y `events_and_observers` eran compartidas con otros paquetes.
  Ya no se usa la caché al arrancar.
- La aplicación cargaba sus `routes/web.php` y `api.php` una vez más por este paquete.
  Cada registro con verificación de correo mandaba el correo repetido. Las dos cosas
  venían de heredar de los proveedores de Foundation.
- La configuración no se cargaba: `mergeConfigFrom` estaba comentado.

### Notas

- La errata del nombre `read.noification` se conserva y se documenta. Laravel no admite
  un segundo nombre para la misma ruta.
