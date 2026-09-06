<?php

namespace Kata\Modules;

use Illuminate\Filesystem\Filesystem;

/**
 * Renderiza stubs del package aplicando reemplazos de placeholders.
 */
final class StubRenderer
{
    public function __construct(
        private readonly Filesystem $files,
        private readonly string $stubsPath,
    ) {
    }

    /**
     * Renderiza un stub relativo aplicando los reemplazos indicados.
     *
     * @param  array<string, string>  $replacements
     */
    public function render(string $relativeStub, array $replacements): string
    {
        $stub = $this->files->get($this->stubsPath . '/' . $relativeStub);

        return strtr($stub, $replacements);
    }

    /**
     * Construye el mapa de placeholders a partir del nombre del módulo.
     *
     * @return array<string, string>
     */
    public function replacementsFor(ModuleName $name): array
    {
        return [
            '{{ namespace }}' => $name->providerNamespace(),
            '{{ class }}' => $name->providerClass(),
            '{{ module }}' => $name->studly,
            '{{ nameKebab }}' => $name->kebab(),
            '{{ nameLower }}' => $name->lower(),
        ];
    }
}
