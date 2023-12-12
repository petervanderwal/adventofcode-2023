<?php

declare(strict_types=1);

namespace App\Puzzle;

use App\Model\PuzzleInput;

class Day02 extends AbstractPuzzle
{
    protected function doCalculateAssignment1(PuzzleInput $input): int|string
    {
        $games = $this->parseInput($input);

        $answer = 0;
        foreach ($games as $gameNr => $grabs) {
            $possible = true;
            foreach ($grabs as $grab) {
                if ($grab['red'] > 12 || $grab['green'] > 13 || $grab['blue'] > 14) {
                    $possible = false;
                    break;
                }
            }

            if ($possible) {
                $answer += $gameNr;
            }
        }

        return $answer;
    }

    protected function doCalculateAssignment2(PuzzleInput $input): int|string
    {
        $games = $this->parseInput($input);

        $answer = 0;
        foreach ($games as $gameNr => $grabs) {
            $minimalNeeded = ['red' => 0, 'green' => 0, 'blue' => 0];
            foreach ($grabs as $grab) {
                foreach ($grab as $color => $amount) {
                    $minimalNeeded[$color] = max($minimalNeeded[$color], $amount);
                }
            }

            $answer += $minimalNeeded['red'] * $minimalNeeded['green'] * $minimalNeeded['blue'];
        }

        return $answer;
    }

    /**
     * @return array<int, array<int, array{
     *              red: int,
     *              green: int,
     *              blue: int,
     *          }>>
     */
    private function parseInput(PuzzleInput $input): array
    {
        $games = [];
        foreach ($input->split("\n") as $line) {
            $line = (string)$line;
            if (!preg_match('/^Game ([0-9]+): (.+)$/', $line, $matches)) {
                throw new \UnexpectedValueException('Line doesn\'t match game', 231202082516);
            }
            $games[$matches[1]] = array_map($this->parseGame(...), explode('; ', $matches[2]));
        }
        return $games;
    }

    /**
     * @return array{
     *              red: int,
     *              green: int,
     *              blue: int,
     *          }
     */
    private function parseGame(string $game): array
    {
        $result = ['red' => 0, 'green' => 0, 'blue' => 0];
        foreach (explode(', ', $game) as $grab) {
            if (!preg_match('/^([0-9]+) (red|green|blue)$/', $grab, $matches)) {
                throw new \UnexpectedValueException('Grab doesn\'t match number+color: "' . $grab . '"', 231202083004);
            }
            $result[$matches[2]] = $matches[1];
        }
        return $result;
    }
}
