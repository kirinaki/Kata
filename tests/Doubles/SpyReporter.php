<?php

namespace Kata\Kata\Tests\Doubles;

use Kata\Kata\Support\Reporter;

/**
 * Reporter que captura los mensajes en memoria para poder inspeccionarlos.
 */
final class SpyReporter implements Reporter
{
    /** @var array<int, string> */
    public array $messages = [];

    public function info(string $message): void
    {
        $this->messages[] = $message;
    }

    public function line(string $message): void
    {
        $this->messages[] = $message;
    }

    public function warn(string $message): void
    {
        $this->messages[] = $message;
    }

    public function raw(string $text): void
    {
        $this->messages[] = $text;
    }

    /** Devuelve todos los mensajes concatenados. */
    public function all(): string
    {
        return implode("\n", $this->messages);
    }
}
