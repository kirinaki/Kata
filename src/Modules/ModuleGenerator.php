<?php

namespace Kata\Modules;

use Illuminate\Filesystem\Filesystem;
use Kata\Enums\Scaffold;
use Kata\Support\Reporter;

/**
 * Orquesta la generación de un módulo a partir de un scaffold.
 *
 * Coordina el renderizado de stubs, la escritura de archivos, el registro
 * del provider y la instalación de dependencias, delegando cada
 * responsabilidad a una clase especializada.
 */
final class ModuleGenerator
{
    public function __construct(
        private readonly Filesystem $files,
        private readonly StubRenderer $stubs,
        private readonly ProviderRegistry $providers,
        private readonly InertiaInstaller $inertia,
        private readonly string $basePath,
    ) {
    }

    /**
     * Genera el módulo. Devuelve true si se completó correctamente.
     */
    public function generate(ModuleName $name, Scaffold $scaffold, Reporter $reporter): bool
    {
        $modulePath = $this->basePath . '/' . $name->relativePath();
        $replacements = $this->stubs->replacementsFor($name);

        $this->writeServiceProvider($name, $scaffold, $modulePath, $replacements, $reporter);
        $this->writeDirectories($name, $scaffold, $modulePath, $reporter);
        $this->writeFiles($name, $scaffold, $modulePath, $replacements, $reporter);

        if ($scaffold->requiresInertia()) {
            $this->inertia->ensureInstalled($reporter);
        }

        return true;
    }

    /**
     * Indica si el módulo ya existe en disco.
     */
    public function exists(ModuleName $name): bool
    {
        return $this->files->isDirectory($this->basePath . '/' . $name->relativePath());
    }

    /**
     * @param  array<string, string>  $replacements
     */
    private function writeServiceProvider(ModuleName $name, Scaffold $scaffold, string $modulePath, array $replacements, Reporter $reporter): void
    {
        $providerFile = "{$modulePath}/Providers/{$name->providerClass()}.php";
        $content = $this->stubs->render($scaffold->providerStub(), $replacements);

        $this->files->ensureDirectoryExists(dirname($providerFile));
        $this->files->put($providerFile, $content);
        $reporter->line("Creado: {$name->relativePath()}/Providers/{$name->providerClass()}.php");

        if ($this->providers->register($name->providerFqcn())) {
            $reporter->line("Registrado en bootstrap/providers.php: {$name->providerFqcn()}");
        } else {
            $reporter->warn("No se pudo registrar automáticamente. Agrega manualmente: {$name->providerFqcn()}");
        }
    }

    private function writeDirectories(ModuleName $name, Scaffold $scaffold, string $modulePath, Reporter $reporter): void
    {
        foreach ($scaffold->directories() as $directory) {
            $path = "{$modulePath}/{$directory}";
            $this->files->ensureDirectoryExists($path);
            $this->files->put("{$path}/.gitkeep", '');
            $reporter->line("Creado: {$name->relativePath()}/{$directory}/");
        }
    }

    /**
     * @param  array<string, string>  $replacements
     */
    private function writeFiles(ModuleName $name, Scaffold $scaffold, string $modulePath, array $replacements, Reporter $reporter): void
    {
        foreach ($scaffold->files() as $stub => $destination) {
            $content = $this->stubs->render($stub, $replacements);
            $target = "{$modulePath}/{$destination}";

            $this->files->ensureDirectoryExists(dirname($target));
            $this->files->put($target, $content);
            $reporter->line("Creado: {$name->relativePath()}/{$destination}");
        }
    }
}
