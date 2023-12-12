<?php

declare(strict_types=1);

namespace App\Puzzle;

use App\Model\PuzzleInput;
use App\Utility\NumberUtility;
use App\Utility\RegexUtility;
use App\Utility\StringUtility;

class Day12 extends AbstractPuzzle
{
    protected function doCalculateAssignment1(PuzzleInput $input): int|string
    {
        $result = 0;
        foreach ($this->parseInput($input) as [$record, $group]) {
            $result += $this->getAmountOfValidCombinations($record, ...$group);
        }
        return $result;
    }

    protected function doCalculateAssignment2(PuzzleInput $input): int|string
    {
        $result = 0;
        foreach ($this->progressService->iterateWithProgressBar($this->parseInput($input)) as [$record, $group]) {
            // Repeat record 5 times, separated by '?'
            $record = str_repeat($record . '?', 4) . $record;
            // Repeat group 5 times
            $group = array_merge(...array_fill(0, 5, $group));
            // And do the same calculation
            $result += $this->getAmountOfValidCombinations($record, ...$group);
        }
        return $result;
    }

    /**
     * @return array<int, array{0: string, 1: int[]}>
     */
    private function parseInput(PuzzleInput $input): array
    {
        return $input->mapLines(function (string $line) {
            [$record, $groups] = explode(' ', $line);
            return [$record, NumberUtility::getNumbersFromLine($groups)];
        });
    }

    private function getAmountOfValidCombinations(mixed $record, int ...$groups): int
    {
        $minGroupsLength = max(0, array_sum($groups) + count($groups) - 1);
        if ($minGroupsLength > strlen($record)) {
            // No options possible
            return 0;
        }

        $minAmountOfPrependSpaces =
            empty($groups) ? strlen($record) : strlen(RegexUtility::extractAll('/^\.+/', $record)[0] ?? '');
        $firstHashSymbol = strpos($record, '#');
        $maxAmountOfPrependSpaces = min(
            strlen($record) - $minGroupsLength,
            $firstHashSymbol === false ? PHP_INT_MAX : $firstHashSymbol
        );

        $result = 0;
        $currentGroup = array_shift($groups);
        for ($amountOfPrependSpaces = $minAmountOfPrependSpaces; $amountOfPrependSpaces <= $maxAmountOfPrependSpaces; $amountOfPrependSpaces++) {
            $prepend = StringUtility::repeat('.', $amountOfPrependSpaces) . StringUtility::repeat('#', $currentGroup ?? 0);
            if (!empty($groups)) {
                $prepend .= '.';
            }

            if (!$this->doesMatch($prepend, substr($record, 0, strlen($prepend)))) {
                continue;
            }

            $result += $currentGroup === null ? 1 : $this->getAmountOfValidCombinations(substr($record, strlen($prepend)), ...$groups);
        }
        return $result;
    }

    private function doesMatch(string $record, string $pattern): bool
    {
        if (strlen($record) !== strlen($pattern)) {
            return false;
        }

        foreach (str_split($record) as $position => $character) {
            if (!in_array($pattern[$position], ['?', $character])) {
                return false;
            }
        }

        return true;
    }
}
