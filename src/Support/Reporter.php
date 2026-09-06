<?php

namespace Kata\Kata\Support;

/**
 * Abstracción de salida para desacoplar los servicios de la consola.
 */
interface Reporter
{
    public function info(string $message): void;

    public function line(string $message): void;

    public function warn(string $message): void;

    /** Escribe texto crudo sin formato ni salto de línea añadido. */
    public function raw(string $text): void;
}
