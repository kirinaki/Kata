<?php

namespace Kata\Modules;

use Inertia\Inertia;
use Kata\Composer\ComposerRunner;
use Kata\Support\Reporter;

/**
 * Garantiza que la dependencia inertiajs/inertia-laravel esté disponible.
 */
final class InertiaInstaller
{
    private const PACKAGE = 'inertiajs/inertia-laravel';

    public function __construct(
        private readonly ComposerRunner $composer,
    ) {
    }

    /**
     * Instala Inertia si no está presente. Devuelve true si está disponible
     * al terminar (ya estaba o se instaló correctamente).
     */
    public function ensureInstalled(Reporter $reporter): bool
    {
        if (class_exists(Inertia::class)) {
            return true;
        }

        $reporter->warn('El paquete ' . self::PACKAGE . ' no está instalado.');
        $reporter->info('Instalando ' . self::PACKAGE . '...');

        $installed = $this->composer->require(
            self::PACKAGE,
            fn (string $buffer) => $reporter->raw($buffer),
        );

        if ($installed) {
            $reporter->info('Instalado: ' . self::PACKAGE);

            return true;
        }

        $reporter->warn('No se pudo instalar automáticamente. Ejecútalo manualmente:');
        $reporter->line('    composer require ' . self::PACKAGE);

        return false;
    }
}
