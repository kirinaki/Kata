<?php

namespace Kata\Tests\Unit;

use Kata\Enums\Scaffold;
use PHPUnit\Framework\TestCase;

class ScaffoldTest extends TestCase
{
    public function test_valores_disponibles(): void
    {
        $this->assertSame(
            ['basic', 'core', 'frontend-ssr', 'frontend-spa', 'frontend-ssr-islands-react'],
            Scaffold::values(),
        );
    }

    public function test_try_from_reconoce_valores_validos(): void
    {
        $this->assertSame(Scaffold::Basic, Scaffold::tryFrom('basic'));
        $this->assertSame(Scaffold::FrontendSpa, Scaffold::tryFrom('frontend-spa'));
        $this->assertNull(Scaffold::tryFrom('noexiste'));
    }

    public function test_solo_spa_requiere_inertia(): void
    {
        $this->assertTrue(Scaffold::FrontendSpa->requiresInertia());
        $this->assertFalse(Scaffold::Basic->requiresInertia());
        $this->assertFalse(Scaffold::Core->requiresInertia());
        $this->assertFalse(Scaffold::FrontendSsr->requiresInertia());
        $this->assertFalse(Scaffold::FrontendSsrIslandsReact->requiresInertia());
    }

    public function test_core_define_carpeta_de_migraciones(): void
    {
        $this->assertContains('Database/Migrations', Scaffold::Core->directories());
        $this->assertSame([], Scaffold::Basic->directories());
    }

    public function test_spa_incluye_middleware_y_pagina(): void
    {
        $files = Scaffold::FrontendSpa->files();

        $this->assertContains('Http/Middlewares/HandleInertiaRequests.php', $files);
        $this->assertContains('Resources/Pages/Home/Page.tsx', $files);
    }

    public function test_islands_incluye_isla_entry_y_vistas(): void
    {
        $files = Scaffold::FrontendSsrIslandsReact->files();

        $this->assertContains('View/Components/Island.php', $files);
        $this->assertContains('Resources/Views/components/island.blade.php', $files);
        $this->assertContains('Resources/Islands/Counter.tsx', $files);
        $this->assertContains('Resources/Assets/app.tsx', $files);
    }

    public function test_cada_scaffold_tiene_stub_de_provider(): void
    {
        foreach (Scaffold::cases() as $scaffold) {
            $this->assertStringEndsWith('service-provider.stub', $scaffold->providerStub());
        }
    }

    public function test_los_stubs_de_cada_scaffold_existen_en_disco(): void
    {
        $stubsPath = dirname(__DIR__, 2) . '/stubs';

        foreach (Scaffold::cases() as $scaffold) {
            $this->assertFileExists("{$stubsPath}/{$scaffold->providerStub()}");

            foreach ($scaffold->files() as $stub => $destination) {
                $this->assertFileExists("{$stubsPath}/{$stub}");
            }
        }
    }
}
