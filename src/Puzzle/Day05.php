<?php

declare(strict_types=1);

namespace App\Puzzle;

use App\Model\Day05\Range;
use App\Model\Day05\RangeMapCollection;
use App\Model\PuzzleInput;
use App\Utility\NumberUtility;

class Day05 extends AbstractPuzzle
{
    public function calculateAssignment1(PuzzleInput $input): int|string
    {
        [$seeds, $maps] = $this->parseInput($input);
        $result = PHP_INT_MAX;
        foreach ($seeds as $seed) {
            $result = min($result, $maps->solve($seed));
        }
        return $result;
    }

    public function calculateAssignment2(PuzzleInput $input): int|string
    {
        [$seedPairs, $maps] = $this->parseInput($input);
        $allRangesSourceStartPoints = $maps->getAllRangesSourceStartPoints();

        $result = PHP_INT_MAX;
        foreach (array_chunk($seedPairs, 2) as [$seedStart, $seedLength]) {
            $seedsToCheck = array_unique([
                $seedStart,
                ...array_filter($allRangesSourceStartPoints, fn (int $rangeStart) => $rangeStart > $seedStart && $rangeStart < $seedStart + $seedLength),
            ]);

            foreach ($seedsToCheck as $seed) {
                $result = min($result, $maps->solve($seed));
            }
        }
        return $result;
    }

    /**
     * @return array{0: int[], 1: RangeMapCollection}
     */
    private function parseInput(PuzzleInput $input): array
    {
        $lines = $input->split("\n");

        $seeds = NumberUtility::getNumbersFromLine(array_shift($lines));

        $maps = new RangeMapCollection();
        $currentMap = null;
        foreach ($lines as $line) {
            if ($line->isEmpty()) {
                continue;
            }

            if ($line->containsAny('map')) {
                $currentMap = $maps->startNewMap();
                continue;
            }

            $currentMap->append(new Range(...NumberUtility::getNumbersFromLine($line)));
        }

        return [$seeds, $maps];
    }
}
