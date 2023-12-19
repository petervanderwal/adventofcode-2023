<?php

declare(strict_types=1);

namespace App\Puzzle;

use App\Model\Day19\Condition;
use App\Model\Day19\ConditionSet;
use App\Model\Day19\RangeCondition;
use App\Model\Day19\WorkflowSet;
use App\Model\PuzzleInput;
use App\Utility\NumberUtility;
use Symfony\Component\String\UnicodeString;

class Day19 extends AbstractPuzzle
{
    protected function doCalculateAssignment1(PuzzleInput $input): int
    {
        [$workflowSet, $parts] = $this->parseInput($input);
        $matchingParts = array_filter($parts, $workflowSet->solve(...));
        return array_sum(array_map(array_sum(...), $matchingParts));
    }

    protected function doCalculateAssignment2(PuzzleInput $input): int
    {
        [$workflowSet] = $this->parseInput($input);

        $entrance = (new ConditionSet())
            ->and(Condition::fromString('x>0'))
            ->and(Condition::fromString('x<4001'))
            ->and(Condition::fromString('m>0'))
            ->and(Condition::fromString('m<4001'))
            ->and(Condition::fromString('a>0'))
            ->and(Condition::fromString('a<4001'))
            ->and(Condition::fromString('s>0'))
            ->and(Condition::fromString('s<4001'));

        return array_sum(
            array_map(
                fn (ConditionSet $conditionSet) => $conditionSet->getSize(),
                $workflowSet->getAcceptingConditions($entrance)
            )
        );
    }

    /**
     * @param PuzzleInput $input
     * @return array{
     *          0: WorkflowSet,
     *          1: array<int, array{x: int, m: int, a: int, s: int}>
     *     }
     */
    private function parseInput(PuzzleInput $input): array
    {
        [$workflowSet, $parts] = $input->split("\n\n");
        return [
            WorkflowSet::fromString((string)$workflowSet),
            array_map(
                fn (UnicodeString $line) => array_combine(
                    ['x', 'm', 'a', 's'],
                    NumberUtility::getNumbersFromLine($line)
                ),
                $parts->split("\n")
            )
        ];
    }
}
