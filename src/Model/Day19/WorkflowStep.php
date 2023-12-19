<?php

declare(strict_types=1);

namespace App\Model\Day19;

class WorkflowStep
{
    public function __construct(
        private Condition $condition,
        private string $destination,
    ) {}

    public static function fromString(string $string): self
    {
        [$condition, $destination] = explode(':', $string);
        return new self(
            Condition::fromString($condition),
            $destination
        );
    }

    public function getCondition(): Condition
    {
        return $this->condition;
    }

    public function getDestination(): string
    {
        return $this->destination;
    }

    public function solve(array $part): ?string
    {
        return $this->condition->matches($part) ? $this->destination : null;
    }

    public function updateDestination(string $from, string $to): bool
    {
        if ($this->destination !== $from) {
            return false;
        }

        $this->destination = $to;
        return true;
    }
}
