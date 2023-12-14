<?php

declare(strict_types=1);

namespace App\Puzzle;

use App\Model\Direction;
use App\Model\Matrix;
use App\Model\Point;
use App\Model\PuzzleInput;

class Day14 extends AbstractPuzzle
{
    protected function doCalculateAssignment1(PuzzleInput $input): int|string
    {
        $grid = Matrix::read($input);
        $result = 0;

        /** @var Point $point */
        foreach ($grid->whereEquals('O')->keys() as $point) {
            $moveTo = $point;

            do {
                $tryMoveTo = $moveTo->moveDirection(Direction::NORTH);
            } while (
                $grid->hasPoint($tryMoveTo)
                && $grid->getPoint($tryMoveTo) === '.'
                && ($moveTo = $tryMoveTo)
            );

            $grid->setPoint($point, '.');
            $grid->setPoint($moveTo, 'O');
            $result += $grid->getNumberOfRows() - $moveTo->getRow();
        }

        return $result;
    }

    protected function doCalculateAssignment2(PuzzleInput $input): int|string
    {
        // TODO: Implement calculateAssignment2() method.
    }
}
