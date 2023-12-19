<?php

declare(strict_types=1);

namespace App\Puzzle;

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
        // Brute force will take 12.5 years to solve
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
