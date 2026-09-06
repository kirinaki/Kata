<?php

namespace Kata\Composer;

/**
 * Abstracción para ejecutar operaciones de Composer.
 */
interface ComposerRunner
{
    /**
     * Ejecuta "composer require {package}".
     *
     * @param  callable(string):void|null  $onOutput  Callback opcional para el output en streaming.
     * @return bool  true si el comando terminó con éxito.
     */
    public function require(string $package, ?callable $onOutput = null): bool;
}
