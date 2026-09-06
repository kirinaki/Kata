<?php

namespace Kata\Tests\Unit;

use Kata\Modules\InertiaInstaller;
use Kata\Tests\Doubles\FakeComposerRunner;
use Kata\Tests\Doubles\SpyReporter;
use PHPUnit\Framework\TestCase;

class InertiaInstallerTest extends TestCase
{
    public function test_instala_inertia_via_composer_cuando_falta(): void
    {
        // En el entorno de test Inertia no está instalado, así que el
        // installer debe delegar en el ComposerRunner.
        if (class_exists(\Inertia\Inertia::class)) {
            $this->markTestSkipped('Inertia ya está instalado en este entorno.');
        }

        $composer = new FakeComposerRunner(result: true);
        $installer = new InertiaInstaller($composer);
        $reporter = new SpyReporter();

        $ok = $installer->ensureInstalled($reporter);

        $this->assertTrue($ok);
        $this->assertSame(['inertiajs/inertia-laravel'], $composer->required);
        $this->assertStringContainsString('Instalado: inertiajs/inertia-laravel', $reporter->all());
    }

    public function test_informa_fallo_si_composer_no_puede_instalar(): void
    {
        if (class_exists(\Inertia\Inertia::class)) {
            $this->markTestSkipped('Inertia ya está instalado en este entorno.');
        }

        $composer = new FakeComposerRunner(result: false);
        $installer = new InertiaInstaller($composer);
        $reporter = new SpyReporter();

        $ok = $installer->ensureInstalled($reporter);

        $this->assertFalse($ok);
        $this->assertStringContainsString('No se pudo instalar', $reporter->all());
        $this->assertStringContainsString('composer require inertiajs/inertia-laravel', $reporter->all());
    }
}
