<?php

declare(strict_types=1);

namespace App\Model\Day19;

use App\Utility\ArrayUtility;

class Workflow
{
    /** @var WorkflowStep[] */
    private array $workflowSteps;

    public function __construct(
        private string $finalDestination,
        WorkflowStep ...$workflowSteps
    ) {
        $this->workflowSteps = $workflowSteps;
    }

    public static function fromString(string $string): self
    {
        $steps = explode(',', $string);
        $finalDestination = array_pop($steps);
        return (new self(
            $finalDestination,
            ...array_map(fn (string $step) => WorkflowStep::fromString($step), $steps)
        ))->simplify();
    }

    public function simplify(): static
    {
        // Remove all steps at the end that have the same destination as our final destination
        while (
            !empty($this->workflowSteps)
            && ArrayUtility::last($this->workflowSteps)->getDestination() === $this->finalDestination
        ) {
            array_pop($this->workflowSteps);
        }

        return $this;
    }

    public function getFinalDestination(): string
    {
        return $this->finalDestination;
    }

    public function isEmpty(): bool
    {
        return empty($this->workflowSteps);
    }

    public function updateDestinations(string $from, string $to): bool
    {
        $result = false;

        if ($this->finalDestination === $from) {
            $this->finalDestination = $to;
            $result = true;
        }

        foreach ($this->workflowSteps as $step) {
            $result = $step->updateDestination($from, $to) || $result;
        }

        if ($result) {
            $this->simplify();
        }

        return $result;
    }

    public function solve(array $part): string
    {
        foreach ($this->workflowSteps as $step) {
            if (null !== $result = $step->solve($part)) {
                return $result;
            }
        }
        return $this->finalDestination;
    }
}
