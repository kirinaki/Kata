<?php

namespace Kata\Kata\Console;

use Illuminate\Console\Command;
use Kata\Kata\Facades\Kata;

class HelloCommand extends Command
{
    /**
     * Firma del comando.
     *
     * @var string
     */
    protected $signature = 'kata:hello {name? : Nombre a saludar}';

    /**
     * Descripción del comando.
     *
     * @var string
     */
    protected $description = 'Comando de ejemplo del package Kata';

    /**
     * Ejecutar el comando.
     */
    public function handle(): int
    {
        $this->info(Kata::greet($this->argument('name')));
        $this->line('Versión de Kata: ' . Kata::version());

        return self::SUCCESS;
    }
}
