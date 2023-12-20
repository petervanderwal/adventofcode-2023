<?php

declare(strict_types=1);

namespace App\Model\Day20;

class Conjunction extends AbstractModule
{
    /**
     * @var array<string, Pulse>
     */
    private array $sourcesStates = [];

    public function addSource(ModuleInterface $module): void
    {
        $this->sourcesStates[$module->getName()] = Pulse::LOW;
    }

    public function sendPulse(ModuleInterface $source, Pulse $pulse): Pulse
    {
        $this->sourcesStates[$source->getName()] = $pulse;
        return in_array(Pulse::LOW, $this->sourcesStates) ? Pulse::HIGH : Pulse::LOW;
    }
}
