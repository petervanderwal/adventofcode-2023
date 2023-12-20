<?php

declare(strict_types=1);

namespace App\Model\Day20;

abstract class AbstractModule implements ModuleInterface
{
    /**
     * @var ModuleInterface[]
     */
    private array $destinations = [];

    public function __construct(
        private readonly string $name,
    ) {}

    public function getName(): string
    {
        return $this->name;
    }

    public function addDestination(ModuleInterface $module): void
    {
        $this->destinations[] = $module;
        $module->addSource($this);
    }

    public function getDestinations(): array
    {
        return $this->destinations;
    }

    public function addSource(ModuleInterface $module): void
    {
        // Most modules don't care, do nothing within the abstract
    }
}
