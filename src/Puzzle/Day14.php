<?php

declare(strict_types=1);

namespace App\Puzzle;

use App\Model\Direction;
use App\Model\Matrix;
use App\Model\Point;
use App\Model\PuzzleInput;

class Day14 extends AbstractPuzzle
{
    protected function doCalculateAssignment1(PuzzleInput $input): int
    {
        $grid = Matrix::read($input);
        $this->tilt($grid, Direction::NORTH);
        return $this->calculateGridData($grid)[0];
    }

    protected function doCalculateAssignment2(PuzzleInput $input): int
    {
        $grid = Matrix::read($input);
        [$score, $state] = $this->calculateGridData($grid);
        $repeatScores = [$score];
        $repeatStates = [$state];
        $maxCycles = 1000000000;

        foreach ($this->progressService->iterateWithProgressBar($this->iterateSteps(1, $maxCycles)) as $cycle) {
            $this->cycle($grid);

            [$score, $state] = $this->calculateGridData($grid);

            // Try to find a loop
            if (false !== $loopStart = array_search($state, $repeatStates)) {
                $loopLength = $cycle - $loopStart;
                $positionWithinLoop = ($maxCycles - $loopStart) % $loopLength;
                return $repeatScores[$positionWithinLoop + $loopStart];
            }

            $repeatScores[] = $score;
            $repeatStates[] = $state;
        }

        return $this->calculateGridData($grid)[0];
    }

    private function cycle(Matrix $grid): void
    {
        foreach ([Direction::NORTH, Direction::WEST, Direction::SOUTH, Direction::EAST] as $direction) {
            $this->tilt($grid, $direction);
        }
    }

    private function tilt(Matrix $grid, Direction $direction): void
    {
        $points = $grid->whereEquals('O')->keys();
        if ($direction === Direction::SOUTH || $direction === Direction::EAST) {
            $points = $points->reverse();
        }

        /** @var Point $point */
        foreach ($points as $point) {
            $moveTo = $point;

            do {
                $tryMoveTo = $moveTo->moveDirection($direction);
            } while (
                $grid->hasPoint($tryMoveTo)
                && $grid->getPoint($tryMoveTo) === '.'
                && ($moveTo = $tryMoveTo)
            );

            $grid->setPoint($point, '.');
            $grid->setPoint($moveTo, 'O');
        }
    }

    /**
     * @return array{0: int, 1: string}
     */
    private function calculateGridData(Matrix $grid): array
    {
        $score = 0;
        $points = [];

        /** @var Point $point */
        foreach ($grid->whereEquals('O')->keys() as $point) {
            $score += $grid->getNumberOfRows() - $point->getRow();
            $points[] = (string)$point;
        }

        return [$score, implode('|', $points)];
    }
}
