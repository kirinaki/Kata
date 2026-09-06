<?php

namespace Kata\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class InstallCommand extends Command
{
    /**
     * Firma del comando.
     *
     * @var string
     */
    protected $signature = 'kata:install';

    /**
     * Descripción del comando.
     *
     * @var string
     */
    protected $description = 'Instala Kata: crea la carpeta modules/ y la registra en el autoload del composer.json';

    /**
     * Ejecutar el comando.
     */
    public function handle(Filesystem $files): int
    {
        $this->createModulesDirectory($files);
        $this->registerAutoload($files);

        $this->newLine();
        $this->info('Kata instalado. Ejecuta "composer dump-autoload" para aplicar el autoload.');

        return self::SUCCESS;
    }

    /**
     * Crea la carpeta modules/ en la raíz del proyecto.
     */
    protected function createModulesDirectory(Filesystem $files): void
    {
        $path = base_path('modules');

        if ($files->isDirectory($path)) {
            $this->line('La carpeta modules/ ya existe.');

            return;
        }

        $files->makeDirectory($path, 0755, true);
        $files->put($path . '/.gitkeep', '');

        $this->line('Carpeta modules/ creada.');
    }

    /**
     * Agrega el namespace Modules\ al autoload PSR-4 del composer.json,
     * junto a App\.
     */
    protected function registerAutoload(Filesystem $files): void
    {
        $composerPath = base_path('composer.json');
        $composer = json_decode($files->get($composerPath), true);

        if (isset($composer['autoload']['psr-4']['Modules\\'])) {
            $this->line('El namespace Modules\\ ya está registrado en el autoload.');

            return;
        }

        $psr4 = $composer['autoload']['psr-4'] ?? [];

        // Reconstruye el mapa insertando Modules\ justo después de App\.
        $updated = [];
        foreach ($psr4 as $namespace => $directory) {
            $updated[$namespace] = $directory;

            if ($namespace === 'App\\') {
                $updated['Modules\\'] = 'modules/';
            }
        }

        // Si no existía App\, agrega Modules\ al final.
        if (! isset($updated['Modules\\'])) {
            $updated['Modules\\'] = 'modules/';
        }

        $composer['autoload']['psr-4'] = $updated;

        $files->put(
            $composerPath,
            json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL
        );

        $this->line('Namespace Modules\\ agregado al autoload del composer.json.');
    }
}
