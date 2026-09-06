<?php

namespace Kata\Tests\Unit;

use Illuminate\Filesystem\Filesystem;
use Kata\Enums\Scaffold;
use Kata\Modules\InertiaInstaller;
use Kata\Modules\ModuleGenerator;
use Kata\Modules\ModuleName;
use Kata\Modules\StubRenderer;
use Kata\Tests\Doubles\FakeComposerRunner;
use Kata\Tests\Doubles\FakeProviderRegistry;
use Kata\Tests\Doubles\SpyReporter;
use PHPUnit\Framework\TestCase;

class ModuleGeneratorTest extends TestCase
{
    private Filesystem $files;
    private string $basePath;
    private FakeProviderRegistry $providers;

    protected function setUp(): void
    {
        parent::setUp();

        $this->files = new Filesystem();
        $this->basePath = sys_get_temp_dir() . '/kata-test-' . uniqid();
        $this->files->ensureDirectoryExists($this->basePath);
        $this->providers = new FakeProviderRegistry();
    }

    protected function tearDown(): void
    {
        $this->files->deleteDirectory($this->basePath);

        parent::tearDown();
    }

    private function generator(): ModuleGenerator
    {
        $stubsPath = dirname(__DIR__, 2) . '/stubs';

        return new ModuleGenerator(
            $this->files,
            new StubRenderer($this->files, $stubsPath),
            $this->providers,
            new InertiaInstaller(new FakeComposerRunner()),
            $this->basePath,
        );
    }

    public function test_scaffold_basic_crea_provider_y_lo_registra(): void
    {
        $name = ModuleName::fromInput('Blog');

        $this->generator()->generate($name, Scaffold::Basic, new SpyReporter());

        $providerFile = "{$this->basePath}/modules/Blog/Providers/BlogServiceProvider.php";
        $this->assertFileExists($providerFile);

        $content = $this->files->get($providerFile);
        $this->assertStringContainsString('namespace Modules\\Blog\\Providers;', $content);
        $this->assertStringContainsString('class BlogServiceProvider', $content);

        // El provider se registró (en el fake).
        $this->assertContains(
            'Modules\\Blog\\Providers\\BlogServiceProvider',
            $this->providers->registered,
        );
    }

    public function test_scaffold_core_crea_carpeta_de_migraciones(): void
    {
        $name = ModuleName::fromInput('Catalog');

        $this->generator()->generate($name, Scaffold::Core, new SpyReporter());

        $this->assertDirectoryExists("{$this->basePath}/modules/Catalog/Database/Migrations");
        $this->assertStringContainsString(
            "loadMigrationsFrom",
            $this->files->get("{$this->basePath}/modules/Catalog/Providers/CatalogServiceProvider.php"),
        );
    }

    public function test_scaffold_spa_genera_estructura_fsd_sin_placeholders(): void
    {
        $name = ModuleName::fromInput('Dashboard');

        $this->generator()->generate($name, Scaffold::FrontendSpa, new SpyReporter());

        $base = "{$this->basePath}/modules/Dashboard";

        // Estructura FSD.
        foreach (['App', 'Pages', 'Widgets', 'Features', 'Shared'] as $dir) {
            $this->assertDirectoryExists("{$base}/Resources/{$dir}");
        }

        // Archivos clave.
        $this->assertFileExists("{$base}/Resources/App/app.tsx");
        $this->assertFileExists("{$base}/Resources/Pages/Home/Page.tsx");
        $this->assertFileExists("{$base}/Http/Middlewares/HandleInertiaRequests.php");

        // No quedan placeholders sin reemplazar.
        $appTsx = $this->files->get("{$base}/Resources/App/app.tsx");
        $this->assertStringNotContainsString('{{ module }}', $appTsx);
        $this->assertStringContainsString('Dashboard', $appTsx);
    }

    public function test_exists_detecta_modulo_creado(): void
    {
        $name = ModuleName::fromInput('Blog');
        $generator = $this->generator();

        $this->assertFalse($generator->exists($name));

        $generator->generate($name, Scaffold::Basic, new SpyReporter());

        $this->assertTrue($generator->exists($name));
    }
}
