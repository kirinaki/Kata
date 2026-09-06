<?php

namespace Kata\Kata\Tests\Doubles;

use Kata\Kata\Modules\ProviderRegistry;

/**
 * Doble de prueba de ProviderRegistry: mantiene el registro en memoria
 * sin tocar bootstrap/providers.php.
 */
final class FakeProviderRegistry implements ProviderRegistry
{
    /** @var array<int, string> */
    public array $registered = [];

    public function register(string $providerFqcn): bool
    {
        $this->registered[] = $providerFqcn;

        return true;
    }

    public function unregister(string $providerFqcn): bool
    {
        $this->registered = array_values(
            array_filter($this->registered, fn ($p) => $p !== $providerFqcn),
        );

        return true;
    }

    public function isRegistered(string $providerFqcn, string $bootstrapPath): bool
    {
        return in_array($providerFqcn, $this->registered, true);
    }
}
