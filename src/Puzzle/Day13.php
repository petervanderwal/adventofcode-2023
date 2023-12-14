<?php

declare(strict_types=1);

namespace App\Puzzle;

use App\Model\Matrix;
use App\Model\Point;
use App\Model\PuzzleInput;
use Symfony\Component\String\UnicodeString;

class Day13 extends AbstractPuzzle
{
    protected function doCalculateAssignment1(PuzzleInput $input): int
    {
        $result = 0;
        foreach ($this->parseInput($input) as $matrix) {
            $result += $this->calculateColumnMirrorInMatrix($matrix)
                + 100 * $this->calculateRowMirrorInMatrix($matrix);
        }
        return $result;
    }

    protected function doCalculateAssignment2(PuzzleInput $input): int|string
    {
        $result = 0;
        /** @var Matrix $matrix */
        foreach ($this->progressService->iterateWithProgressBar($this->parseInput($input)) as $matrix) {
            $originalColumnScore = $this->calculateColumnMirrorInMatrix($matrix);
            $originalRowScore = $this->calculateRowMirrorInMatrix($matrix);
            $smudgeFound = false;
            foreach ($matrix->keys() as $smudge) {
                $original = $matrix->getPoint($smudge);
                $matrix->setPoint($smudge, $original === '.' ? '#': '.');
                $columnScore = $this->calculateColumnMirrorInMatrix($matrix, $originalColumnScore);
                $rowScore = $this->calculateRowMirrorInMatrix($matrix, $originalRowScore);
                $matrix->setPoint($smudge, $original);

                if ($columnScore !== 0) {
                    $result += $columnScore;
                    $smudgeFound = true;
                }
                if ($rowScore !== 0) {
                    $result += 100 * $rowScore;
                    $smudgeFound = true;
                }
                if ($smudgeFound) {
                    break;
                }
            }
            if (!$smudgeFound) {
                throw new \UnexpectedValueException("No smudge found in matrix\n" . $matrix->plot(), 231214214619);
            }
        }
        return $result;
    }

    /**
     * @return Matrix[]
     */
    private function parseInput(PuzzleInput $input): array
    {
        return array_map(
            fn (UnicodeString $string) => Matrix::read($string),
            $input->split("\n\n")
        );
    }

    private function calculateColumnMirrorInMatrix(Matrix $matrix, ?int $ignoreColumn = null): int
    {
        return $this->calculateLinesBeforeMirror(
            $ignoreColumn,
            ...$matrix->getColumns()->map(fn (Matrix\Column $column) => (string)$column)->toArray()
        );
    }

    private function calculateRowMirrorInMatrix(Matrix $matrix, ?int $ignoreRow = null): int
    {
        return $this->calculateLinesBeforeMirror(
            $ignoreRow,
            ...$matrix->getRows()->map(fn (Matrix\Row $row) => (string)$row)->toArray()
        );
    }

    private function calculateLinesBeforeMirror(?int $ignoreLine, string ...$lines): int
    {
        $mirrors = [];
        $previousLine = null;
        foreach ($lines as $index => $line) {
            foreach ($mirrors as $mirrorIndex => $mirrorStart) {
                $lineIndexToCheck = $mirrorStart - ($index - $mirrorStart) - 1;
                if ($lineIndexToCheck >= 0 && $lines[$lineIndexToCheck] !== $line) {
                    // Mirror broke
                    unset($mirrors[$mirrorIndex]);
                }
            }

            if ($index !== $ignoreLine && $line === $previousLine) {
                $mirrors[] = $index;
            }

            $previousLine = $line;
        }
        return array_pop($mirrors) ?? 0;
    }
}
