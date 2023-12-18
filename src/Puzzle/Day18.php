<?php

declare(strict_types=1);

namespace App\Puzzle;

use App\Model\Direction;
use App\Model\Matrix;
use App\Model\Point;
use App\Model\PuzzleInput;

class Day18 extends AbstractPuzzle
{
    protected function doCalculateAssignment1(PuzzleInput $input): int
    {
        $matrix = $this->buildMatrix($input);

        $isEmpty = fn(?string $color) => $color === null;
        $innerAreaSize = (int)$matrix->getAreas($isEmpty, $isEmpty)
            ->where(fn (Matrix\Area $area) => !$area->isBorderArea())
            ->map(fn (Matrix\Area $area) => $area->getSize())
            ->sum();

        $borderAreaSize = count($matrix->where(fn (?string $color) => $color !== null));

        return $innerAreaSize + $borderAreaSize;
    }

    protected function doCalculateAssignment2(PuzzleInput $input): int|string
    {
        // TODO: Implement calculateAssignment2() method.
    }

    private function buildMatrix(PuzzleInput $input): Matrix
    {
        $lines = $this->parseInput($input);

        $points = [$currentPoint = new Point(0, 0)];
        $colors = [];
        foreach ($lines as [$direction, $amount, $color]) {
            for ($i = 0; $i < $amount; $i++) {
                $points[] = $currentPoint = $currentPoint->moveDirection($direction);
                $colors[(string)$currentPoint] = $color;
            }
        }

        return Matrix::createFromPoints(
            true,
            fn () => null,
            fn (Point $point) => $colors[(string)$point],
            ...$points
        );
    }

    /**
     * @return array<int, array{0: Direction, 1: int, 2: string}>
     */
    private function parseInput(PuzzleInput $input): array
    {
        return $input->mapLines(
            function (string $line) {
                [$direction, $amount, $color] = explode(' ', $line);
                return [
                    match ($direction) {
                        'U' => Direction::NORTH,
                        'R' => Direction::EAST,
                        'D' => Direction::SOUTH,
                        'L' => Direction::WEST,
                    },
                    (int)$amount,
                    trim($color, '()'),
                ];
            }
        );
    }
}
