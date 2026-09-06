<?php

namespace Kata\Modules;

use Illuminate\Filesystem\Filesystem;
use Kata\Support\Reporter;

/**
 * Elimina un módulo y lo desregistra del bootstrap de providers.
 */
final class ModuleRemover
{
    public function __construct(
        private readonly Filesystem $files,
        private readonly ProviderRegistry $providers,
        private readonly string $basePath,
        private readonly string $bootstrapProvidersPath,
    ) {
    }

    public function directoryExists(ModuleName $name): bool
    {
        return $this->files->isDirectory($this->modulePath($name));
    }

    public function providerRegistered(ModuleName $name): bool
    {
        return $this->providers->isRegistered($name->providerFqcn(), $this->bootstrapProvidersPath);
    }

    /**
     * Elimina el módulo. Desregistra el provider primero para no dejar
     * referencias huérfanas que rompan el arranque de Laravel.
     */
    public function remove(ModuleName $name, Reporter $reporter): void
    {
        if ($this->providerRegistered($name)) {
            $this->providers->unregister($name->providerFqcn());
            $reporter->line("Desregistrado de bootstrap/providers.php: {$name->providerFqcn()}");
        }

        if ($this->directoryExists($name)) {
            $this->files->deleteDirectory($this->modulePath($name));
            $reporter->line("Eliminada: {$name->relativePath()}/");
        }
    }

    private function modulePath(ModuleName $name): string
    {
        return $this->basePath . '/' . $name->relativePath();
    }
}
