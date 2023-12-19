<?php

declare(strict_types=1);

namespace App\Model\Day19;

class WorkflowSet
{
    public const ENTRANCE_WORKFLOW = 'in';
    public const DESTINATION_ACCEPT = 'A';
    public const DESTINATION_REJECT = 'R';

    /**
     * @var array<string, Workflow>
     */
    private array $workflows;

    public function __construct(array $workflows)
    {
        $this->workflows = $workflows;
    }

    public static function fromString(string $string): self
    {
        $workflows = [];
        foreach (explode("\n", $string) as $line) {
            [$name, $workflow] = explode('{', $line);
            $workflows[$name] = Workflow::fromString(rtrim($workflow, '}'));
        }
        return (new self($workflows))->simplify();
    }

    public function simplify(): static
    {
        $emptyWorkflows = [];
        foreach ($this->workflows as $name => $workflow) {
            if ($workflow->isEmpty()) {
                $emptyWorkflows[] = $name;
            }
        }

        while (!empty($emptyWorkflows)) {
            $nameOfEmptyWorkflow = array_pop($emptyWorkflows);
            if ($nameOfEmptyWorkflow === self::ENTRANCE_WORKFLOW) {
                continue;
            }

            // Remove workflow and update all other workflow destinations to this final destination
            $destinationOfEmptyWorkflow = $this->workflows[$nameOfEmptyWorkflow]->getFinalDestination();
            unset($this->workflows[$nameOfEmptyWorkflow]);
            foreach ($this->workflows as $nameOfOtherWorkflow => $otherWorkflow) {
                if (
                    $otherWorkflow->updateDestinations($nameOfEmptyWorkflow, $destinationOfEmptyWorkflow)
                    && $otherWorkflow->isEmpty()
                ) {
                    $emptyWorkflows[] = $nameOfOtherWorkflow;
                }
            }
        }

        return $this;
    }

    public function solve(array $part): bool
    {
        $nameOfWorkflow = static::ENTRANCE_WORKFLOW;
        while ($nameOfWorkflow !== static::DESTINATION_ACCEPT && $nameOfWorkflow !== static::DESTINATION_REJECT) {
            $nameOfWorkflow = $this->workflows[$nameOfWorkflow]->solve($part);
        }
        return $nameOfWorkflow === static::DESTINATION_ACCEPT;
    }
}
