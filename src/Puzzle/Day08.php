<?php

declare(strict_types=1);

namespace App\Puzzle;

use App\Model\Day08\CycleData;
use App\Model\Iterator\ArrayIterator;
use App\Model\Iterator\RepeatedIterator;
use App\Model\Iterator\StringCharacterIterator;
use App\Model\Parallel\Task;
use App\Model\Parallel\TaskSet;
use App\Model\PuzzleInput;
use App\Utility\ArrayUtility;
use App\Utility\MathUtility;
use App\Utility\RegexUtility;

class Day08 extends AbstractPuzzle
{
    protected function doCalculateAssignment1(PuzzleInput $input): int
    {
        [$instructions, $graph] = $this->parseInput($input);

        $position = 'AAA';
        $steps = 0;
        foreach (new RepeatedIterator(new StringCharacterIterator($instructions)) as $instruction) {
            $position = $graph[$position][$instruction];
            $steps++;

            if ($position === 'ZZZ') {
                return $steps;
            }
        }
    }

    protected function doCalculateAssignment2(PuzzleInput $input): int
    {
        return $input->isDemoInput() ? $this->calculateAssignment2Smart($input) : $this->calculateAssignment2UgglyLCM($input);
    }

    private function calculateAssignment2BruteForce(PuzzleInput $input): int
    {
        [$instructions, $graph] = $this->parseInput($input);

        $positions = array_filter(array_keys($graph), fn (string $node) => str_ends_with($node, 'A'));
        $steps = 0;
        foreach (new RepeatedIterator(new StringCharacterIterator($instructions)) as $instruction) {
            $positions = array_map(fn (string $node) => $graph[$node][$instruction], $positions);
            $steps++;

            if ((new ArrayIterator($positions))->all(fn ($node) => str_ends_with($node, 'Z'))) {
                return $steps;
            }
        }
    }

    private function calculateAssignment2Smart(PuzzleInput $input): int
    {
        [$instructions, $graph] = $this->parseInput($input);

        $startPositions = array_filter(array_keys($graph), fn (string $node) => str_ends_with($node, 'A'));

        /**  @var array{pathStart: string, zPositions: int[], cycleStart: int, cycleLength: int}[] $allCycleData */
        $allCycleData = array_map(
            fn (string $position) => $this->calculateCycleLengthAndZPositions($graph, $instructions, $position),
            $startPositions
        );

        $smallestCycle = min(array_map(fn (array $cycleData) => $cycleData['cycleLength'], $allCycleData));
        $breakPoints = [
            0,
            ...array_unique(array_map(fn (array $cycleData) => $cycleData['cycleStart'], $allCycleData))
        ];

        for ($break = 0; ; $break++) {
            if ($break < count($breakPoints)) {
                // Loop over our custom breakpoints first
                $step = $breakPoints[$break];
            } else {
                // Then increase the step with the smallest cycle length each round
                $step += $smallestCycle;
            }

            $upcomingZPositions = [];
            foreach ($allCycleData as $cycleData) {
                if ($step < $cycleData['cycleStart'] + $cycleData['cycleLength']) {
                    $upcomingZPositions[] = $cycleData['zPositions'];
                    continue;
                }

                $positionInCycle = ($step - $cycleData['cycleStart']) % $cycleData['cycleLength'];
                $upcomingZPositions[$cycleData['pathStart']] = array_map(
                    fn (int $position)  => (
                        ($position - $cycleData['cycleStart'])
                        - $positionInCycle
                        + $cycleData['cycleLength']
                    ) % $cycleData['cycleLength'] + $step,
                    array_filter($cycleData['zPositions'], fn (int $position) => $position > $cycleData['cycleStart'])
                );
            }

            if (null !== $result = array_values(array_intersect(...array_values($upcomingZPositions)))[0] ?? null) {
                return $result;
            }
        }
    }

    /**
     * @param array $graph
     * @param string $instructions
     * @param string $position
     * @return array{pathStart: string, zPositions: int[], cycleStart: int, cycleLength: int}
     */
    private function calculateCycleLengthAndZPositions(array $graph, string $instructions, string $position): array
    {
        $path = [$position];
        $zPositions = [];

        foreach (new RepeatedIterator(new StringCharacterIterator($instructions)) as $instruction) {
            $position = $graph[$position][$instruction];
            foreach (ArrayUtility::searchAll($path, $position) as $cycleStart) {
                $cycleLength = count($path) - $cycleStart;
                if ($cycleLength % strlen($instructions) !== 0) {
                    // This is a cycle, but not a never-ending one as this isn't a full length of instructions
                    continue;
                }

                if (empty($zPositions)) {
                    throw new \UnexpectedValueException('No zPosition in path ' . implode(' > ', $path) . ' > ' . $position, 231208173735);
                }

                return [
                    'pathStart' => $path[0],
                    'zPositions' => $zPositions,
                    'cycleStart' => $cycleStart,
                    'cycleLength' => count($path) - $cycleStart
                ];
            }

            $path[] = $position;
            if (str_ends_with($position, 'Z')) {
                $zPositions[] = count($path) - 1;
            }
        }
    }

    private function calculateAssignment2UgglyLCM(PuzzleInput $input): int
    {
        [$instructions, $graph] = $this->parseInput($input);

        $startPositions = array_filter(array_keys($graph), fn (string $node) => str_ends_with($node, 'A'));

        $allCycleData = $this->calculateCycleLengthAndZPositionsOptimized($instructions, $graph, ...$startPositions);

        // This calculation is not correct - it only works because the input data is crafted as such
        // https://www.reddit.com/r/adventofcode/comments/18e6vdf/2023_day_8_part_2_an_explanation_for_why_the/
        $cycleLengths = array_unique(array_map(fn (CycleData $cycleData) => $cycleData->cycleLength, $allCycleData));
        $greatestCommonDivisor = MathUtility::greatestCommonDivisor(...$cycleLengths);
        $fullCycleLength = ArrayUtility::multiply(array_map(fn (int $cycleLength) => $cycleLength / $greatestCommonDivisor, $cycleLengths)) * $greatestCommonDivisor;
        return $fullCycleLength;
    }

    /**
     * @param array<string, array{L: string, R: string}> $graph
     * @return CycleData[]
     */
    private function calculateCycleLengthAndZPositionsOptimized(string $instructions, array $graph, string ...$startPositions): array
    {
        $fullLoops = [];
        foreach (array_keys($graph) as $loop) {
            $position = $loop;
            foreach (new StringCharacterIterator($instructions) as $instruction) {
                $fullLoops[$loop][] = $position = $graph[$position][$instruction];
            }
        }

        return array_map(
            fn (string $position) => $this->calculateCycleLengthAndZPositionsFromLoops($fullLoops, $position),
            $startPositions
        );
    }

    /**
     * @param array<string, string[]> $fullLoops
     */
    private function calculateCycleLengthAndZPositionsFromLoops(array $fullLoops, string $position): CycleData
    {
        $endsWithZ = fn(string $position) => str_ends_with($position, 'Z');

        $pathStart = $position;
        $paths = [];
        for ($i = 0; ; $i++) {
            $pathToAdd = $fullLoops[$position];
            $position = ArrayUtility::last($pathToAdd);
            if (false !== $loopStart = array_search($pathToAdd, $paths, true)) {
                $beforePath = [$pathStart, ...array_merge(...array_slice($paths, 0, $loopStart))];
                $cyclePath = array_merge(...array_slice($paths, $loopStart));
                return new CycleData(
                    $pathStart,
                    ArrayUtility::searchAll($beforePath, $endsWithZ),
                    ArrayUtility::searchAll($cyclePath, $endsWithZ),
                    count($beforePath),
                    count($cyclePath),
                );
            }

            $paths[] = $pathToAdd;
        }
    }

    /**
     * @return CycleData[]
     */
    private function normalizeCycleData(CycleData ...$cycleData): array
    {
        if (count($cycleData) < 2) {
            return $cycleData;
        }

        // Move all cycles so that they have the same start positions
        $maxCycleStart = max(...array_map(fn (CycleData $cycleData) => $cycleData->cycleStart, $cycleData));
        $cycleData = array_map(fn (CycleData $cycleData) => $cycleData->moveCycleStart($maxCycleStart), $cycleData);

        // Repeat all cycles zo that they all have the same length
        $cycleLengths = array_unique(array_map(fn (CycleData $cycleData) => $cycleData->cycleLength, $cycleData));
        $greatestCommonDivisor = MathUtility::greatestCommonDivisor(...$cycleLengths);
        $fullCycleLength = ArrayUtility::multiply(array_map(fn (int $cycleLength) => $cycleLength / $greatestCommonDivisor, $cycleLengths)) * $greatestCommonDivisor;
        var_dump($greatestCommonDivisor, $fullCycleLength);
        $cycleData[0]->increaseCycleLength($fullCycleLength);
        if ($fullCycleLength > 100) die();
        return array_map(fn (CycleData $cycleData) => $cycleData->increaseCycleLength($fullCycleLength), $cycleData);
    }

    /**
     * @param PuzzleInput $input
     * @return array{
     *          0: string,
     *          1: array<string, array{L: string, R: string}>
     *     }
     */
    private function parseInput(PuzzleInput $input): array
    {
        $lines = $input->split("\n");

        $instructions = (string)array_shift($lines);
        array_shift($lines); // Blank line

        $graph = [];
        foreach ($lines as $line) {
            [$from, $left, $right] = RegexUtility::extractAll('/[A-Z0-9]+/', $line);
            $graph[$from] = ['L' => $left, 'R' => $right];
        }
        return [$instructions, $graph];
    }
}
