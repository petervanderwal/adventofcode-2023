<?php

declare(strict_types=1);

namespace App\Model\Day20;

class FlipFlop extends AbstractModule
{
    private bool $isOn = false;

    public function sendPulse(ModuleInterface $source, Pulse $pulse): ?Pulse
    {
        return match($pulse) {
            Pulse::HIGH => null,
            Pulse::LOW => ($this->isOn = !$this->isOn) ? Pulse::HIGH : Pulse::LOW,
        };
    }
}
