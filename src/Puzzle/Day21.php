<?php

declare(strict_types=1);

namespace App\Puzzle;

use App\Model\Direction;
use App\Model\Matrix;
use App\Model\Point;
use App\Model\PuzzleInput;

class Day21 extends AbstractPuzzle
{
    protected function doCalculateAssignment1(PuzzleInput $input): int|string
    {
        /** @var array<string, Point> $currentPositions */
        $currentPositions = [];
        /** @var string[] $accessiblePositions */
        $accessiblePositions = [];

        foreach (Matrix::read($input)->where(fn (string $char) => $char !== '#') as $point => $char) {
            $accessiblePositions[] = (string)$point;
            if ($char === 'S') {
                $currentPositions[(string)$point] = $point;
            }
        }

        foreach ($this->progressService->iterateWithProgressBar(
            range(1, $input->isDemoInput() ? 6 : 64)
        ) as $step) {
            $newPositions = [];
            foreach ($currentPositions as $position) {
                foreach (Direction::straightCases() as $direction) {
                    $newPosition = $position->moveDirection($direction);
                    $key = (string)$newPosition;
                    if (in_array($key, $accessiblePositions)) {
                        $newPositions[$key] = $newPosition;
                    }
                }
            }
            $currentPositions = $newPositions;
        }

        return count($currentPositions);
    }

    protected function doCalculateAssignment2(PuzzleInput $input): int|string
    {
        // TODO: Implement calculateAssignment2() method.
    }
}
