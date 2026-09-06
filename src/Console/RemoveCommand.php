<?php

namespace Kata\Console;

use Illuminate\Console\Command;
use Kata\Modules\ModuleName;
use Kata\Modules\ModuleRemover;
use Kata\Support\ConsoleReporter;

class RemoveCommand extends Command
{
    /**
     * Firma del comando.
     *
     * @var string
     */
    protected $signature = 'kata:remove {nombre : Nombre del módulo a eliminar} {--force : Elimina sin pedir confirmación}';

    /**
     * Descripción del comando.
     *
     * @var string
     */
    protected $description = 'Elimina un módulo y lo quita del registro de providers';

    /**
     * Ejecutar el comando.
     */
    public function handle(ModuleRemover $remover): int
    {
        $name = ModuleName::fromInput($this->argument('nombre'));

        if (! $remover->directoryExists($name) && ! $remover->providerRegistered($name)) {
            $this->error("El módulo '{$name}' no existe.");

            return self::FAILURE;
        }

        if (! $this->option('force') && ! $this->confirm("¿Seguro que deseas eliminar el módulo '{$name}'? Esta acción no se puede deshacer.", false)) {
            $this->warn('Operación cancelada.');

            return self::FAILURE;
        }

        $remover->remove($name, new ConsoleReporter($this));

        $this->newLine();
        $this->info("Módulo '{$name}' eliminado.");

        return self::SUCCESS;
    }
}
