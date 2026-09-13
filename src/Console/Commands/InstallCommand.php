<?php

namespace Innoboxrr\LaravelNotifications\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

/**
 * Las notificaciones de base de datos necesitan la tabla `notifications`, y una
 * aplicacion nueva de Laravel no la trae. `make:notifications-table` la publica,
 * pero falla si la migracion ya existe, asi que no se puede llamar a ciegas
 * desde un instalador. Este comando si: publica la migracion solo si falta.
 */
class InstallCommand extends Command
{
    protected $signature = 'notifications:install';

    protected $description = 'Publish the notifications table migration when the application does not have it yet';

    public function handle(): int
    {
        if ($existing = $this->existingMigration()) {
            $this->components->info("The notifications migration already exists [{$existing}].");

            return self::SUCCESS;
        }

        if ($this->tableExists()) {
            $this->components->info('The notifications table already exists.');

            return self::SUCCESS;
        }

        return $this->call('make:notifications-table');
    }

    private function existingMigration(): ?string
    {
        $matches = glob($this->laravel->databasePath('migrations/*_create_notifications_table.php')) ?: [];

        return $matches === [] ? null : basename($matches[0]);
    }

    /**
     * Una aplicacion que todavia no configuro su base de datos no puede
     * responder; en ese caso se publica la migracion, que es lo que falta.
     */
    private function tableExists(): bool
    {
        try {
            return Schema::hasTable('notifications');
        } catch (\Throwable) {
            return false;
        }
    }
}
