<?php

namespace Kata\Tests\Feature;

use Illuminate\View\Compilers\BladeCompiler;
use Kata\KataServiceProvider;
use Kata\Modules\ModuleName;
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

    public function test_kata_create_islands_genera_y_registra_el_modulo(): void
    {
        $name = 'ItIslands' . random_int(1000, 9999);

        $this->artisan("kata:create {$name} frontend-ssr-islands-react")
            ->assertExitCode(0);

        $this->assertFileExists($this->modulePath($name) . "/Providers/{$name}ServiceProvider.php");
        $this->assertFileExists($this->modulePath($name) . '/View/Components/Island.php');
        $this->assertFileExists($this->modulePath($name) . '/Resources/Views/components/island.blade.php');
        $this->assertFileExists($this->modulePath($name) . '/Resources/Islands/Counter.tsx');
        $this->assertFileExists($this->modulePath($name) . '/Resources/Assets/app.tsx');

        $providers = require base_path('bootstrap/providers.php');
        $this->assertContains("Modules\\{$name}\\Providers\\{$name}ServiceProvider", $providers);

        $this->cleanup($name);

        $this->assertDirectoryDoesNotExist($this->modulePath($name));
    }

    public function test_island_renderiza_modo_mount_hydrate_y_alias_namespaced(): void
    {
        $name = 'ItIsland' . random_int(1000, 9999);
        $lower = ModuleName::fromInput($name)->lower();

        $this->artisan("kata:create {$name} frontend-ssr-islands-react")
            ->assertExitCode(0);

        // Las clases del módulo no están autoloaded en el esqueleto de Testbench:
        // se cargan a mano y el provider se registra en la app ya booteada
        // (su boot() corre al instante).
        require base_path("modules/{$name}/View/Components/Island.php");
        require base_path("modules/{$name}/Providers/{$name}ServiceProvider.php");

        $this->app->register("Modules\\{$name}\\Providers\\{$name}ServiceProvider");

        try {
            $mount = BladeCompiler::render(
                "<!-- {$name} mount -->\n<x-island component=\"Counter\" :props=\"['initial' => 5]\" />",
                [],
                true,
            );

            $this->assertStringContainsString('data-island="Counter"', $mount);
            $this->assertStringContainsString("data-props='{\"initial\":5}'", $mount);
            $this->assertStringContainsString('data-mode="mount"', $mount);

            $hydrate = BladeCompiler::render(
                "<!-- {$name} hydrate -->\n<x-island component=\"Counter\" :props=\"['initial' => 10]\">cargando…</x-island>",
                [],
                true,
            );

            $this->assertStringContainsString('data-mode="hydrate"', $hydrate);
            $this->assertMatchesRegularExpression(
                '/<div[^>]*data-mode="hydrate"[^>]*>\s*cargando…\s*<\/div>/',
                $hydrate,
            );

            $namespaced = BladeCompiler::render(
                "<!-- {$name} ns -->\n<x-{$lower}::island component=\"Counter\" :props=\"['initial' => 15]\" />",
                [],
                true,
            );

            $this->assertStringContainsString('data-island="Counter"', $namespaced);
            $this->assertStringContainsString('data-mode="mount"', $namespaced);
        } finally {
            $this->cleanup($name);
        }
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