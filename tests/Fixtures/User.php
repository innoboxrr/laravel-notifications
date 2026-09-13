<?php

namespace Innoboxrr\LaravelNotifications\Tests\Fixtures;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * El usuario de una aplicacion nueva: el modelo de Laravel con Notifiable, que
 * es lo unico que el paquete le pide.
 */
class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'users';

    protected $guarded = [];
}
