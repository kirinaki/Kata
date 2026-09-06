<?php

namespace Kata\Kata\Composer;

use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;
use Throwable;

/**
 * Ejecuta Composer mediante Symfony Process.
 */
final class ProcessComposerRunner implements ComposerRunner
{
    public function __construct(
        private readonly string $workingDirectory,
        private readonly int $timeout = 300,
    ) {
    }

    public function require(string $package, ?callable $onOutput = null): bool
    {
        $composer = $this->findComposer();

        if ($composer === null) {
            return false;
        }

        $process = Process::fromShellCommandline(
            "{$composer} require " . escapeshellarg($package) . ' --no-interaction',
            $this->workingDirectory,
        );
        $process->setTimeout($this->timeout);

        try {
            $process->run(function ($type, $buffer) use ($onOutput): void {
                if ($onOutput !== null) {
                    $onOutput($buffer);
                }
            });
        } catch (Throwable) {
            return false;
        }

        return $process->isSuccessful();
    }

    /**
     * Localiza el ejecutable de Composer (composer.phar local o en PATH).
     */
    private function findComposer(): ?string
    {
        $phar = $this->workingDirectory . '/composer.phar';

        if (file_exists($phar)) {
            return '"' . PHP_BINARY . '" ' . $phar;
        }

        return (new ExecutableFinder())->find('composer');
    }
}
