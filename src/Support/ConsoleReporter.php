<?php

namespace Kata\Kata\Support;

use Illuminate\Console\Command;

/**
 * Reporter que escribe a través de un comando de Artisan.
 */
final class ConsoleReporter implements Reporter
{
    public function __construct(
        private readonly Command $command,
    ) {
    }

    public function info(string $message): void
    {
        $this->command->info($message);
    }

    public function line(string $message): void
    {
        $this->command->line($message);
    }

    public function warn(string $message): void
    {
        $this->command->warn($message);
    }

    public function raw(string $text): void
    {
        $this->command->getOutput()->write($text);
    }
}
