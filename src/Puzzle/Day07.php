<?php

declare(strict_types=1);

namespace App\Puzzle;

use App\Model\Day07\Hand;
use App\Model\PuzzleInput;

class Day07 extends AbstractPuzzle
{
    public function calculateAssignment1(PuzzleInput $input): int|string
    {
        $games = $this->parseInput($input);
        usort($games, fn (array $a, $b) => $a[0]->compare($b[0]));

        $result = 0;
        foreach ($games as $rank => [, $bid]) {
            $result += ($rank + 1) * $bid;
        }
        return $result;
    }

    public function calculateAssignment2(PuzzleInput $input): int|string
    {
        // TODO: Implement calculateAssignment2() method.
    }

    /**
     * @param PuzzleInput $input
     * @return array<int, array{0: Hand, 1: int}>
     */
    private function parseInput(PuzzleInput $input): array
    {
        return $input->mapLines(function (string $line) {
            [$hand, $bid] = explode(' ', $line);
            return [new Hand($hand), (int)$bid];
        });
    }
}
