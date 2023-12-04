<?php

declare(strict_types=1);

namespace App\Puzzle;

use App\Model\PuzzleInput;

class Day04 extends AbstractPuzzle
{
    public function calculateAssignment1(PuzzleInput $input): int|string
    {
        $result = 0;
        foreach ($this->parseInput($input) as $card) {
            $amountWinning = count(array_intersect($card[0], $card[1]));
            if ($amountWinning === 0) {
                continue;
            }

            $result += pow(2, $amountWinning - 1);
        }
        return $result;
    }

    public function calculateAssignment2(PuzzleInput $input): int|string
    {
        // TODO: Implement calculateAssignment2() method.
    }

    /**
     * @param PuzzleInput $input
     * @return array<int, array{0: int[], 1: int[]}>
     */
    private function parseInput(PuzzleInput $input): array
    {
        return $input->mapLines(
            function (string $line): array {
                $line = preg_replace('/^Card +[0-9]+: +/', '', $line);
                $sections = explode(' | ', $line);
                return [
                    $this->parseSection($sections[0]),
                    $this->parseSection($sections[1]),
                ];
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
