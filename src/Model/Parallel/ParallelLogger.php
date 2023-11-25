<?php

declare(strict_types=1);

namespace App\Model\Parallel;

use Webmozarts\Console\Parallelization\Logger\DecoratorLogger;

class ParallelLogger extends DecoratorLogger
{
    public function logAdvance(int $steps = 1): void
    {
        // Noop
    }

    public function logFinish(string $itemName): void
    {
        // Noop
    }

    public function logChildProcessStarted(int $index, int $pid, string $commandName): void
    {
        // Noop
    }

    public function logChildProcessFinished(int $index): void
    {
        // Noop
    }
}