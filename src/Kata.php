<?php

namespace Kata\Kata;

class Kata
{
    /**
     * @param  array<string, mixed>  $config
     */
    public function __construct(protected array $config = [])
    {
    }

    /**
     * Devuelve un saludo del package.
     */
    public function greet(?string $name = null): string
    {
        $name = $name ?: ($this->config['default_name'] ?? 'mundo');

        return "¡Hola, {$name}! Kata está funcionando.";
    }

    /**
     * Devuelve la versión del package.
     */
    public function version(): string
    {
        return $this->config['version'] ?? '0.1.0';
    }
}
