<?php

declare(strict_types=1);

namespace App\Puzzle;

use App\Model\PuzzleInput;
use App\Utility\MathUtility;
use App\Utility\NumberUtility;

class Day06 extends AbstractPuzzle
{
    protected function doCalculateAssignment1(PuzzleInput $input): int
    {
        return $this->calculateO1Solution($input);
    }

    protected function doCalculateAssignment2(PuzzleInput $input): int
    {
        return $this->calculateO1Solution($input->replace(' ', ''));
    }

    // Time (for both parts together): 00:00.040
    private function calculateO1Solution(PuzzleInput $input): int
    {
        $result = 1;
        foreach ($this->parseInput($input) as ['time' => $time, 'distance' => $distanceToBeat]) {
            // distance = speed * timeToTravel >= distanceToBeat + 1
            // distance = timeHoldingButton * (time - timeHoldingButton) >= distanceToBeat + 1
            // distance = -(timeHoldingButton^2) + timeHoldingButton * time >= distanceToBeat + 1
            // solve: -(timeHoldingButton^2) + timeHoldingButton * time = distanceToBeat + 1
            // solve: -(timeHoldingButton^2) + timeHoldingButton * time - distanceToBeat - 1 = 0
            // use abc formula: ax^2 + bx + c = 0 => x = (-b +/- sqrt(b^2 - 4*a*c))/(2a)
            // here: x = timeHoldingButton, a = -1, b = time, c = -distanceToBeat - 1

            [$minimumTimeBeatingDistance, $maximumTimeBeatingDistance] = MathUtility::abcFormula(-1, $time, -$distanceToBeat - 1);
            $timeFramesBeatingDistance = (int)(floor($maximumTimeBeatingDistance) - ceil($minimumTimeBeatingDistance) + 1);
            $result *= $timeFramesBeatingDistance;
        }
        return $result;
    }

    // Time (for both parts together): 00:02.352
    private function calculateBruteForceForScience(PuzzleInput $input): int
    {
        $result = 1;
        foreach ($this->parseInput($input) as ['time' => $time, 'distance' => $distanceToBeat]) {
            $gameResult = 0;
            for ($timeHoldingButton = 0; $timeHoldingButton <= $time; $timeHoldingButton++) {
                // distance = speed * timeToTravel >= distanceToBeat + 1
                // distance = timeHoldingButton * (time - timeHoldingButton) >= distanceToBeat + 1
                $distance = $timeHoldingButton * ($time - $timeHoldingButton);
                if ($distance > $distanceToBeat) {
                    $gameResult++;
                }
            }
            $result *= $gameResult;
        }
        return $result;
    }

    /**
     * @param PuzzleInput $input
     * @return array<int, array{time: int, distance: int}>
     */
    private function parseInput(PuzzleInput $input): array
    {
        $lines = $input->split("\n");
        $times = NumberUtility::getNumbersFromLine($lines[0]);
        $distances = NumberUtility::getNumbersFromLine($lines[1]);
        $games = [];
        foreach ($times as $index => $time) {
            $games[] = ['time' => $time, 'distance' => $distances[$index]];
        }
        return $games;
    }
}
