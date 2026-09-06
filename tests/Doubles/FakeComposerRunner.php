<?php

namespace Kata\Kata\Tests\Doubles;

use Kata\Kata\Composer\ComposerRunner;

/**
 * Doble de prueba de ComposerRunner: no ejecuta Composer real, solo
 * registra las llamadas y devuelve un resultado configurable.
 */
final class FakeComposerRunner implements ComposerRunner
{
    /** @var array<int, string> */
    public array $required = [];

    public function __construct(
        private readonly bool $result = true,
    ) {
    }

    public function require(string $package, ?callable $onOutput = null): bool
    {
        $this->required[] = $package;

        return $this->result;
    }
}
