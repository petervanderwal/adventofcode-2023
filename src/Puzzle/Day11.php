<?php

declare(strict_types=1);

namespace App\Puzzle;

use App\Model\Iterator\ArrayIterator;
use App\Model\Matrix;
use App\Model\Point;
use App\Model\PuzzleInput;

class Day11 extends AbstractPuzzle
{
    protected function doCalculateAssignment1(PuzzleInput $input): int
    {
        return $this->calculateSumOfManhattanDistances(...$this->getExpandedGalaxies($input));
    }

    protected function doCalculateAssignment2(PuzzleInput $input): int|string
    {
        return $this->calculateSumOfManhattanDistances(...$this->getExpandedGalaxies(
            $input,
            $input->isDemoInput() ? 100 : 1000000
        ));
    }

    private function calculateSumOfManhattanDistances(Point ...$galaxies): int
    {
        $result = 0;
        for ($i = 0; $i < count($galaxies); $i++) {
            for ($j = $i; $j < count($galaxies); $j++) {
                $result += $galaxies[$i]->getManhattanDistance($galaxies[$j]);
            }
        }
        return $result;
    }

    /**
     * @return Point[]
     */
    private function getExpandedGalaxies(PuzzleInput $input, int $expansionFactor = 2): array
    {
        $map = Matrix::read($input);

        $emptyRows = $this->getEmptyIndexes($map->getRows());
        $emptyColumns = $this->getEmptyIndexes($map->getColumns());

        $galaxies = [];
        /** @var Point $galaxy */
        foreach ($map->whereEquals('#')->keys() as $galaxy) {
            $galaxies[] = $galaxy->moveXY(
                ($expansionFactor - 1) * count($emptyColumns->where(fn (int $x) => $galaxy->x > $x)),
                ($expansionFactor - 1) * count($emptyRows->where(fn (int $y) => $galaxy->y > $y))
            );
        }
        return $galaxies;
    }

    private function getEmptyIndexes(Matrix\AbstractRowColumnMatrixIterator $rowsColumns): ArrayIterator
    {
        return $rowsColumns->where(fn (Matrix\AbstractMatrixRowColumn $rowColumn) => !$rowColumn->hasEquals('#'))
            ->keys()
            ->cacheIterator();
    }
}
