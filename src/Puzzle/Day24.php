<?php

declare(strict_types=1);

namespace App\Puzzle;

use App\Model\Day24\Hailstone;
use App\Model\Math\When;
use App\Model\PuzzleInput;

class Day24 extends AbstractPuzzle
{
    protected function doCalculateAssignment1(PuzzleInput $input): int
    {
        $areaMin = $input->isDemoInput() ? 7 : 200000000000000;
        $areaMax = $input->isDemoInput() ? 27 : 400000000000000;

        $result = 0;

        // I've tested, all these hailstones cross our test area, so no point in pre-filtering
        $hailstones = $this->getHailstones($input);
        foreach ($hailstones as $index => $hailstone) {
            $xyFormula = $hailstone->getXyFormula();
            foreach (array_slice($hailstones, $index + 1) as $otherHailstone) {
                $crossingAtX = $xyFormula->whenCrossing($otherHailstone->getXyFormula());

                if ($crossingAtX === When::ALWAYS) {
                    $result++;
                    continue;
                }

                if (
                    $crossingAtX === When::NEVER // Not crossing
                    || $crossingAtX < $areaMin || $crossingAtX > $areaMax // Not crossing within area
                    || $hailstone->timeXFormula->whenEquals($crossingAtX) < 0 // Crossing in the past of hailstone A
                    || $otherHailstone->timeXFormula->whenEquals($crossingAtX) < 0 // Crossing in the past of hailstone B
                ) {
                    continue;
                }

                $crossingAtY = $xyFormula($crossingAtX);
                if ($crossingAtY >= $areaMin && $crossingAtY <= $areaMax) {
                    $result++;
                }
            }
        }

        return $result;
    }

    protected function doCalculateAssignment2(PuzzleInput $input): int|string
    {
        // TODO: Implement calculateAssignment2() method.
    }


    /**
     * @return Hailstone[]
     */
    private function getHailstones(PuzzleInput $input): array
    {
        return $input->mapLines(Hailstone::fromString(...));
    }
}
