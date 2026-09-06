<?php

namespace Kata\Kata\Tests\Feature;

use Kata\Kata\KataServiceProvider;
use Orchestra\Testbench\TestCase;

/**
 * Tests de integración que ejecutan los comandos kata:* reales contra una
 * aplicación Laravel arrancada (con el KataServiceProvider registrado).
 *
 * Usan Testbench para levantar la app de forma aislada, sin depender del
 * proyecto anfitrión. Cada test crea un módulo con nombre único y lo limpia
 * al finalizar para no dejar residuos.
 */
class ModuleCommandsTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            KataServiceProvider::class,
        ];
    }

    private function modulePath(string $name): string
    {
        return base_path("modules/{$name}");
    }

    private function cleanup(string $name): void
    {
        $this->artisan("kata:remove {$name} --force");
    }

    public function test_kata_create_basic_genera_y_registra_el_modulo(): void
    {
        $name = 'ItBasic' . random_int(1000, 9999);

        $this->artisan("kata:create {$name} basic")
            ->assertExitCode(0);

        $this->assertFileExists($this->modulePath($name) . "/Providers/{$name}ServiceProvider.php");

        // El provider quedó registrado en bootstrap/providers.php.
        $providers = require base_path('bootstrap/providers.php');
        $this->assertContains("Modules\\{$name}\\Providers\\{$name}ServiceProvider", $providers);

        $this->cleanup($name);
    }

    public function test_kata_create_core_incluye_migraciones(): void
    {
        $name = 'ItCore' . random_int(1000, 9999);

        $this->artisan("kata:create {$name} core")->assertExitCode(0);

        $this->assertDirectoryExists($this->modulePath($name) . '/Database/Migrations');

        $this->cleanup($name);
    }

    public function test_kata_create_scaffold_invalido_falla(): void
    {
        $this->artisan('kata:create Foo noexiste')
            ->assertExitCode(1);
    }

    public function test_kata_remove_elimina_y_desregistra(): void
    {
        $name = 'ItRemove' . random_int(1000, 9999);

        $this->artisan("kata:create {$name} basic")->assertExitCode(0);
        $this->assertDirectoryExists($this->modulePath($name));

        $this->artisan("kata:remove {$name} --force")->assertExitCode(0);

        $this->assertDirectoryDoesNotExist($this->modulePath($name));

        $providers = require base_path('bootstrap/providers.php');
        $this->assertNotContains("Modules\\{$name}\\Providers\\{$name}ServiceProvider", $providers);
    }

    public function test_kata_remove_modulo_inexistente_falla(): void
    {
        $this->artisan('kata:remove NoExisteXYZ --force')
            ->assertExitCode(1);
    }
}