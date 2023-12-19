<?php

declare(strict_types=1);

namespace App\Model\Day19;

class WorkflowStep
{
    public function __construct(
        private string $testField,
        private string $condition,
        private int $comparedValue,
        private string $destination,
    ) {}

    public static function fromString(string $string): self
    {
        [$fullCondition, $destination] = explode(':', $string);
        return new self(
            $fullCondition[0],
            $fullCondition[1],
            (int)substr($fullCondition, 2),
            $destination
        );
    }

    public function getDestination(): string
    {
        return $this->destination;
    }

    public function solve(array $part): ?string
    {
        $value = $part[$this->testField];
        return match($this->condition) {
            '>' => $value > $this->comparedValue,
            '<' => $value < $this->comparedValue
        } ? $this->destination : null;
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
