<?php

namespace Kata\Modules;

use Illuminate\Support\ServiceProvider;

/**
 * Registra y desregistra service providers en bootstrap/providers.php.
 */
final class ProviderRegistrar implements ProviderRegistry
{
    /**
     * Registra el provider. Devuelve true si tuvo éxito.
     */
    public function register(string $providerFqcn): bool
    {
        return ServiceProvider::addProviderToBootstrapFile($providerFqcn);
    }

    /**
     * Desregistra el provider. Devuelve true si tuvo éxito.
     */
    public function unregister(string $providerFqcn): bool
    {
        return ServiceProvider::removeProviderFromBootstrapFile($providerFqcn, strict: true);
    }

    /**
     * Indica si el provider está registrado en el bootstrap.
     */
    public function isRegistered(string $providerFqcn, string $bootstrapPath): bool
    {
        if (! is_file($bootstrapPath)) {
            return false;
        }

        return in_array($providerFqcn, (array) require $bootstrapPath, true);
    }
}
