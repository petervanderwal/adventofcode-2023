<?php

declare(strict_types=1);

namespace App\Puzzle;

use App\Model\PuzzleInput;

class Day15 extends AbstractPuzzle
{
    protected function doCalculateAssignment1(PuzzleInput $input): int|string
    {
        $result = 0;
        foreach ($input->split(',') as $string) {
            $result += $this->hash((string)$string);
        }
        return $result;
    }

    protected function doCalculateAssignment2(PuzzleInput $input): int|string
    {
        // TODO: Implement calculateAssignment2() method.
    }

    private function hash(string $string): int
    {
        $result = 0;
        foreach (str_split($string) as $char) {
            $result = (($result + ord($char)) * 17) % 256;
        }
        return $result;
    }
}
