<?php

declare(strict_types=1);

namespace App\Puzzle;

use App\Model\PuzzleInput;

class Day04 extends AbstractPuzzle
{
    protected function doCalculateAssignment1(PuzzleInput $input): int|string
    {
        $result = 0;
        foreach ($this->parseInput($input) as $amountWinning) {
            if ($amountWinning === 0) {
                continue;
            }

            $result += pow(2, $amountWinning - 1);
        }
        return $result;
    }

    protected function doCalculateAssignment2(PuzzleInput $input): int|string
    {
        $result = 0;
        $futureCopies = [];
        foreach ($this->parseInput($input) as $amountWinning) {
            $amountOfCopies = array_shift($futureCopies) ?? 0;
            $result += $amountOfCopies + 1;

            for ($i = 0; $i < $amountWinning; $i++) {
                $futureCopies[$i] = ($futureCopies[$i] ?? 0) + 1 + $amountOfCopies;
            }
        }
        return $result;
    }

    /**
     * @param PuzzleInput $input
     * @return array<int, int}>
     */
    private function parseInput(PuzzleInput $input): array
    {
        return $input->mapLines(
            function (string $line): int {
                $line = preg_replace('/^Card +[0-9]+: +/', '', $line);
                $sections = explode(' | ', $line);
                return count(array_intersect(
                    $this->parseSection($sections[0]),
                    $this->parseSection($sections[1]),
                ));
            }
        );
    }

    /**
     * @return int[]
     */
    private function parseSection(string $section): array
    {
        return array_unique(array_map(
            fn (string $nr) => (int)$nr,
            preg_split('/ +/', $section)
        ));
    }
}
