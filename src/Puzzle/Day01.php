<?php

declare(strict_types=1);

namespace App\Puzzle;

use App\Model\PuzzleInput;

class Day01 extends AbstractPuzzle
{
    protected function doCalculateAssignment1(PuzzleInput $input): int|string
    {
        return array_sum(
            $input->mapLines(
                fn (string $line) => (int)(
                    preg_replace('/^[^0-9]*([0-9]).*/', '\1', $line)
                    . preg_replace('/.*([0-9])[^0-9]*$/', '\1', $line)
                )
            )
        );
    }

    protected function doCalculateAssignment2(PuzzleInput $input): int|string
    {
        return array_sum(
            $input->mapLines(
                function (string $line) {
                    $pattern = 'one|two|three|four|five|six|seven|eight|nine';
                    $numbers = [
                        'one' => '1',
                        'two' => '2',
                        'three' => '3',
                        'four' => '4',
                        'five' => '5',
                        'six' => '6',
                        'seven' => '7',
                        'eight' => '8',
                        'nine' => '9',
                    ];

                    preg_match("/^.*?([0-9]|$pattern)/", $line, $first);
                    $patternReverse = strrev($pattern);
                    preg_match("/^.*?([0-9]|$patternReverse)/", strrev($line), $last);
                    return strtr($first[1], $numbers) . strtr(strrev($last[1]), $numbers);
                }
            )
        );
    }
}
