<?php

namespace Kata\Kata\Tests\Unit;

use Kata\Kata\Enums\Scaffold;
use PHPUnit\Framework\TestCase;

class ScaffoldTest extends TestCase
{
    public function test_valores_disponibles(): void
    {
        $this->assertSame(
            ['basic', 'core', 'frontend-ssr', 'frontend-spa'],
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

    public function test_cada_scaffold_tiene_stub_de_provider(): void
    {
        foreach (Scaffold::cases() as $scaffold) {
            $this->assertStringEndsWith('service-provider.stub', $scaffold->providerStub());
        }
    }
}
