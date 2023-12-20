<?php

declare(strict_types=1);

namespace App\Model\Day20;

class Output extends AbstractModule
{
    public function addDestination(ModuleInterface $module): void
    {
        throw new \BadMethodCallException('Output can\'t have a destination', 231220172321);
    }

    public function sendPulse(ModuleInterface $source, Pulse $pulse): ?Pulse
    {
        return null;
    }
}
