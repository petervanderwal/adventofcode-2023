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
        $boxes = array_fill(0, 256, []);
        foreach ($input->split(',') as $string) {
            $string = (string)$string;
            if (str_ends_with($string, '-')) {
                $label = substr($string, 0, -1);
                $box = $this->hash($label);
                unset($boxes[$box][$label]);
            } else {
                $lens = substr($string, -1);
                $label = substr($string, 0, -2);
                $box = $this->hash($label);
                $boxes[$box][$label] = $lens;
            }
        }

        $result = 0;
        foreach ($boxes as $boxNr => $boxContent) {
            foreach (array_values($boxContent) as $lensPosition => $lensFocalPoint) {
                $result += ($boxNr + 1) * ($lensPosition + 1) * $lensFocalPoint;
            }
        }
        return $result;
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
