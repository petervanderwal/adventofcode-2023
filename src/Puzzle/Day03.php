<?php

declare(strict_types=1);

namespace App\Puzzle;

use App\Model\Matrix;
use App\Model\PuzzleInput;

class Day03 extends AbstractPuzzle
{
    protected function doCalculateAssignment1(PuzzleInput $input): int|string
    {
        $matrix = Matrix::read($input);

        $result = 0;
        foreach ($matrix->matches('/[0-9]+/') as $match) {
            $section = $this->extractSectionSurroundingMatch($matrix, $match);
            if (!$section->matches('/[^.0-9]/')->empty()) {
                $result += (int)$match->getMatch();
            }
        }

        return $result;
    }

    protected function doCalculateAssignment2(PuzzleInput $input): int|string
    {
        $matrix = Matrix::read($input);

        $stars = [];
        foreach ($matrix->matches('/[0-9]+/') as $numberMatch) {
            $section = $this->extractSectionSurroundingMatch($matrix, $numberMatch);

            foreach ($section->matches('/\*/') as $starMatch) {
                $point = $starMatch->getStartCoordinate()->offset($section->getSectionOffset());
                $stars[(string)$point][] = (int)$numberMatch->getMatch();
            }
        }

        $result = 0;
        foreach ($stars as $star) {
            if (count($star) === 2) {
                $result += $star[0] * $star[1];
            }
        }
        return $result;
    }

    private function extractSectionSurroundingMatch(Matrix $matrix, Matrix\MatrixMatch $match): Matrix
    {
        return $matrix->extractSection(
            max($match->getStartCoordinate()->getRow() - 1, 0),
            max($match->getStartCoordinate()->getColumn() - 1, 0),
            min($match->getEndCoordinate()->getRow() + 1, $matrix->getNumberOfRows() - 1),
            min($match->getEndCoordinate()->getColumn() + 1, $matrix->getNumberOfColumns() - 1)
        );
    }
}
