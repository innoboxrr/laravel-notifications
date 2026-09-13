<?php

namespace Innoboxrr\LaravelNotifications\Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Schema;
use Innoboxrr\LaravelNotifications\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

final class InstallCommandTest extends TestCase
{
    private string $databasePath;

    protected function setUp(): void
    {
        parent::setUp();

        // Un database/ propio: la migracion no puede caer dentro del esqueleto
        // de Testbench en vendor/.
        $this->databasePath = sys_get_temp_dir() . '/notifications-install-' . bin2hex(random_bytes(4));
        mkdir($this->databasePath . '/migrations', 0777, true);

        $this->app->useDatabasePath($this->databasePath);
    }

    protected function tearDown(): void
    {
        (new Filesystem)->deleteDirectory($this->databasePath);

        parent::tearDown();
    }

    #[Test]
    public function publica_la_migracion_cuando_falta(): void
    {
        $this->artisan('notifications:install')->assertSuccessful();

        $files = $this->migrations();

        $this->assertCount(1, $files);
        $this->assertStringContainsString("Schema::create('notifications'", (string) file_get_contents($files[0]));
    }

    #[Test]
    public function correrlo_dos_veces_no_duplica_ni_falla(): void
    {
        $this->artisan('notifications:install')->assertSuccessful();
        $this->artisan('notifications:install')->assertSuccessful();

        $this->assertCount(1, $this->migrations());
    }

    #[Test]
    public function la_migracion_publicada_crea_la_tabla(): void
    {
        $this->artisan('notifications:install')->assertSuccessful();

        $this->artisan('migrate', ['--path' => $this->databasePath . '/migrations', '--realpath' => true])
            ->assertSuccessful();

        $this->assertTrue(Schema::hasTable('notifications'));
    }

    #[Test]
    public function no_publica_nada_si_la_tabla_ya_existe(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
        });

        $this->artisan('notifications:install')->assertSuccessful();

        $this->assertSame([], $this->migrations());
    }

    /**
     * @return array<int, string>
     */
    private function migrations(): array
    {
        return glob($this->databasePath . '/migrations/*_create_notifications_table.php') ?: [];
    }
}
