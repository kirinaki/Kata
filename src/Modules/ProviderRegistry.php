<?php

namespace Kata\Kata\Modules;

/**
 * Contrato para registrar/desregistrar service providers.
 */
interface ProviderRegistry
{
    public function register(string $providerFqcn): bool;

    public function unregister(string $providerFqcn): bool;

    public function isRegistered(string $providerFqcn, string $bootstrapPath): bool;
}
