<?php

declare(strict_types=1);

namespace App\Puzzle;

use App\Algorithm\LoopDetection;
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
        return $grid->whereEquals('O')
            ->keys()
            ->map(fn (Point $point) => $grid->getNumberOfRows() - $point->getRow())
            ->sum();
    }

    protected function doCalculateAssignment2(PuzzleInput $input): int
    {
        $loopDetection = new LoopDetection(
            1000000000,
            function (LoopDetection\Step $initialStep) use ($input) {
                $this->populateStepFromGrid($initialStep, Matrix::read($input));
            },
            function (LoopDetection\Step $step, Matrix $grid) {
                $this->populateStepFromGrid($step, $this->cycle($grid));
            }
        );

        $this->progressService->showProgressBar($loopDetection->iterate());

        return $loopDetection->getRepeatingEndScore();
    }

    private function cycle(Matrix $grid): Matrix
    {
        foreach ([Direction::NORTH, Direction::WEST, Direction::SOUTH, Direction::EAST] as $direction) {
            $this->tilt($grid, $direction);
        }
        return $grid;
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

    private function populateStepFromGrid(LoopDetection\Step $step, Matrix $grid): void
    {
        $score = 0;
        $points = [];

        /** @var Point $point */
        foreach ($grid->whereEquals('O')->keys() as $point) {
            $score += $grid->getNumberOfRows() - $point->getRow();
            $points[] = (string)$point;
        }

        $step->setState($grid)
            ->setStateStringRepresentation(implode('|', $points))
            ->setScore($score);
    }
}
