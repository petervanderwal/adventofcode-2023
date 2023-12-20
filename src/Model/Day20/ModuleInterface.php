<?php

declare(strict_types=1);

namespace App\Model\Day20;

interface ModuleInterface
{
    public function getName(): string;

    public function addDestination(ModuleInterface $module): void;

    /**
     * @return ModuleInterface[]
     */
    public function getDestinations(): array;
    public function addSource(ModuleInterface $module): void;

    /**
     * @return Pulse|null The pulse to send to this module destinations
     */
    public function sendPulse(ModuleInterface $source, Pulse $pulse): ?Pulse;
}
