<?php

namespace Kata\Modules;

use Illuminate\Support\Str;

/**
 * Value Object que representa el nombre de un módulo y sus derivaciones.
 */
final class ModuleName
{
    private function __construct(
        public readonly string $studly,
    ) {
    }

    /**
     * Crea el value object normalizando la entrada a StudlyCase.
     */
    public static function fromInput(string $input): self
    {
        return new self(Str::studly($input));
    }

    /**
     * Nombre en kebab-case (para slugs de rutas y assets).
     */
    public function kebab(): string
    {
        return Str::kebab($this->studly);
    }

    /**
     * Nombre en minúsculas (para namespaces de vistas).
     */
    public function lower(): string
    {
        return Str::lower($this->studly);
    }

    /**
     * Namespace raíz del módulo (Modules\{Nombre}).
     */
    public function rootNamespace(): string
    {
        return "Modules\\{$this->studly}";
    }

    /**
     * Namespace del provider (Modules\{Nombre}\Providers).
     */
    public function providerNamespace(): string
    {
        return "{$this->rootNamespace()}\\Providers";
    }

    /**
     * Nombre de la clase del ServiceProvider ({Nombre}ServiceProvider).
     */
    public function providerClass(): string
    {
        return "{$this->studly}ServiceProvider";
    }

    /**
     * FQCN del ServiceProvider del módulo.
     */
    public function providerFqcn(): string
    {
        return "{$this->providerNamespace()}\\{$this->providerClass()}";
    }

    /**
     * Ruta relativa del módulo (modules/{Nombre}).
     */
    public function relativePath(): string
    {
        return "modules/{$this->studly}";
    }

    public function __toString(): string
    {
        return $this->studly;
    }
}
