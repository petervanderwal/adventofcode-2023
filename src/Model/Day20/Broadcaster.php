<?php

declare(strict_types=1);

namespace App\Model\Day20;

class Broadcaster extends AbstractModule
{
    public const NAME = 'broadcaster';

    public function sendPulse(?ModuleInterface $source, Pulse $pulse): Pulse
    {
        return $pulse;
    }
}
