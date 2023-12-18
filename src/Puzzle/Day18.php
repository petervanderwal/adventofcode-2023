<?php

declare(strict_types=1);

namespace App\Puzzle;

use App\Model\Direction;
use App\Model\Point;
use App\Model\PuzzleInput;
use App\Utility\MathUtility;
use App\Utility\NumberUtility;

class Day18 extends AbstractPuzzle
{
    protected function doCalculateAssignment1(PuzzleInput $input): int
    {
        return $this->calculate($input, fn (string $column1, string $column2) => [
            match ($column1) {
                'U' => Direction::NORTH,
                'R' => Direction::EAST,
                'D' => Direction::SOUTH,
                'L' => Direction::WEST,
            },
            (int)$column2
        ]);
    }

    protected function doCalculateAssignment2(PuzzleInput $input): int
    {
        return $this->calculate($input, fn (string $column1, string $column2, string $column3) => [
            match ($column3[7]) {
                '0' => Direction::EAST,
                '1' => Direction::SOUTH,
                '2' => Direction::WEST,
                '3' => Direction::NORTH,
            },
            hexdec(substr($column3, 2, 5))
        ]);

    }

    private function calculate(PuzzleInput $input, callable $getInfo): int
    {
        $data = $input->mapLines(fn (string $line) => $getInfo(...explode(' ', $line)));

        $points = [$currentPoint = new Point(0, 0)];
        foreach ($data as $index => [$direction, $amount]) {
            $previousDirection = $data[NumberUtility::positiveModulo($index - 1, count($data))][0];
            $nextDirection = $data[NumberUtility::positiveModulo($index + 1, count($data))][0];

            if ($nextDirection !== $previousDirection) {
                // Any S shape
                //   .#....
                //   .#....
                //   .####.
                //   ....#.
                //   ....#.
                // Always has the exact length as given

                // For any U shape we either need to add 1 or subtract 1
                $amount += match ($direction) {
                    Direction::EAST => $nextDirection === Direction::SOUTH ? 1 : -1,
                    Direction::WEST => $nextDirection === Direction::NORTH ? 1 : -1,
                    Direction::NORTH => $nextDirection === Direction::EAST ? 1 : -1,
                    Direction::SOUTH => $nextDirection === Direction::WEST ? 1 : -1,
                };
            }

            $points[] = $currentPoint = $currentPoint->moveDirection($direction, $amount);
        }

        return (int)MathUtility::shoelaceFormula(...$points);
    }
}
