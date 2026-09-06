<?php

namespace Kata;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;
use Kata\Composer\ComposerRunner;
use Kata\Composer\ProcessComposerRunner;
use Kata\Console\CreateCommand;
use Kata\Console\HelloCommand;
use Kata\Console\InstallCommand;
use Kata\Console\RemoveCommand;
use Kata\Modules\InertiaInstaller;
use Kata\Modules\ModuleGenerator;
use Kata\Modules\ModuleRemover;
use Kata\Modules\ProviderRegistrar;
use Kata\Modules\ProviderRegistry;
use Kata\Modules\StubRenderer;

class KataServiceProvider extends ServiceProvider
{
    /**
     * Registrar bindings del contenedor.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/kata.php', 'kata');

        $this->app->singleton('kata', function ($app) {
            return new Kata($app['config']->get('kata'));
        });

        $this->registerModuleServices();
    }

    /**
     * Registra los servicios de generación/eliminación de módulos.
     */
    protected function registerModuleServices(): void
    {
        $this->app->bind(ComposerRunner::class, function ($app) {
            return new ProcessComposerRunner($app->basePath());
        });

        $this->app->singleton(ProviderRegistrar::class);
        $this->app->bind(ProviderRegistry::class, ProviderRegistrar::class);

        $this->app->singleton(StubRenderer::class, function ($app) {
            return new StubRenderer($app->make(Filesystem::class), __DIR__ . '/../stubs');
        });

        $this->app->singleton(InertiaInstaller::class, function ($app) {
            return new InertiaInstaller($app->make(ComposerRunner::class));
        });

        $this->app->singleton(ModuleGenerator::class, function ($app) {
            return new ModuleGenerator(
                $app->make(Filesystem::class),
                $app->make(StubRenderer::class),
                $app->make(ProviderRegistrar::class),
                $app->make(InertiaInstaller::class),
                $app->basePath(),
            );
        });

        $this->app->singleton(ModuleRemover::class, function ($app) {
            return new ModuleRemover(
                $app->make(Filesystem::class),
                $app->make(ProviderRegistrar::class),
                $app->basePath(),
                $app->getBootstrapProvidersPath(),
            );
        });
    }

    /**
     * Arrancar servicios del package.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/kata.php' => config_path('kata.php'),
            ], 'kata-config');

            $this->commands([
                HelloCommand::class,
                CreateCommand::class,
                InstallCommand::class,
                RemoveCommand::class,
            ]);
        }
    }
}
