<?php

declare(strict_types=1);

namespace App\Puzzle;

use App\Model\PuzzleInput;
use App\Utility\ArrayUtility;
use App\Utility\NumberUtility;

class Day09 extends AbstractPuzzle
{
    protected function doCalculateAssignment1(PuzzleInput $input): int|string
    {
        return $this->calculateAssignment($input, false);
    }

    protected function doCalculateAssignment2(PuzzleInput $input): int|string
    {
        return $this->calculateAssignment($input, true);
    }

    /**
     * @param PuzzleInput $input
     * @return int|null
     */
    public function calculateAssignment(PuzzleInput $input, bool $leftSide): ?int
    {
        $result = 0;
        foreach ($this->parseInput($input) as $lineOfNumbers) {
            $result += $this->determineNextNumberInLine($leftSide, ...$lineOfNumbers);
        }
        return $result;
    }

    private function determineNextNumberInLine(bool $leftSide, int ...$lineOfNumbers)
    {
        if (empty(ArrayUtility::searchAll($lineOfNumbers, fn (int $number) => $number !== 0))) {
            // Line consists of all zeros
            return 0;
        }

        $intervals = [];
        $previous = null;
        foreach ($lineOfNumbers as $number) {
            if ($previous !== null) {
                $intervals[] = $number - $previous;
            }
            $previous = $number;
        }

        $nextInLine = $this->determineNextNumberInLine($leftSide, ...$intervals);
        return $leftSide ? $lineOfNumbers[0] - $nextInLine : $previous + $nextInLine;
    }

    /**
     * @return array<int, array<int, int>>
     */
    private function parseInput(PuzzleInput $input): array
    {
        return $input->mapLines(fn (string $line) => NumberUtility::getNumbersFromLine($line));
    }
}
