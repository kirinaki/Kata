<?php

namespace Kata\Console;

use Illuminate\Console\Command;
use Kata\Enums\Scaffold;
use Kata\Modules\ModuleGenerator;
use Kata\Modules\ModuleName;
use Kata\Support\ConsoleReporter;

class CreateCommand extends Command
{
    /**
     * Firma del comando.
     *
     * @var string
     */
    protected $signature = 'kata:create {nombre : Nombre del módulo a crear} {scaffold : Tipo de scaffold a generar}';

    /**
     * Descripción del comando.
     *
     * @var string
     */
    protected $description = 'Crea un nuevo módulo a partir de un scaffold';

    /**
     * Ejecutar el comando.
     */
    public function handle(ModuleGenerator $generator): int
    {
        $scaffold = Scaffold::tryFrom(strtolower($this->argument('scaffold')));

        if ($scaffold === null) {
            $this->error(
                "Scaffold '{$this->argument('scaffold')}' no soportado. Disponibles: "
                . implode(', ', Scaffold::values())
            );

            return self::FAILURE;
        }

        $name = ModuleName::fromInput($this->argument('nombre'));

        if ($generator->exists($name) && ! $this->confirm("El módulo '{$name}' ya existe. ¿Deseas sobrescribirlo?", false)) {
            $this->warn('Operación cancelada.');

            return self::FAILURE;
        }

        $generator->generate($name, $scaffold, new ConsoleReporter($this));

        $this->newLine();
        $this->info("Módulo '{$name}' creado con el scaffold '{$scaffold->value}'.");

        return self::SUCCESS;
    }
}
