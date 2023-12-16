<?php

declare(strict_types=1);

namespace App\Puzzle;

use App\Model\Day16\Mirror;
use App\Model\Day16\MirrorCell;
use App\Model\Direction;
use App\Model\Matrix;
use App\Model\Point;
use App\Model\PuzzleInput;

class Day16 extends AbstractPuzzle
{
    protected function doCalculateAssignment1(PuzzleInput $input): int
    {
        $matrix = $this->parseInput($input);
        return $this->calculateEnergyLevel($matrix, $matrix->getCornerPoint(Direction::NORTH_WEST), Direction::EAST);
    }

    protected function doCalculateAssignment2(PuzzleInput $input): int
    {
        $matrix = $this->parseInput($input);

        $result = 0;
        foreach ($this->progressService->iterateWithProgressBar(iterator_to_array($matrix->getBorderEntrances())) as $entrance) {
            $matrix->each(fn (MirrorCell $cell) => $cell->reset());
            $result = max($result, $this->calculateEnergyLevel($matrix, $entrance->point, $entrance->direction));
        }
        return $result;
    }

    /**
     * @param Matrix<MirrorCell> $matrix
     */
    private function calculateEnergyLevel(Matrix $matrix, Point $entrancePoint, Direction $entranceDirection): int
    {
        $beams = [[$entrancePoint, $entranceDirection]];
        while (count($beams)) {
            $nextRoundBeams = [];
            foreach ($beams as [$position, $inputDirection]) {
                /** @var Point $position */
                foreach ($matrix->getPoint($position)->beamIn($inputDirection) as $outputDirection) {
                    $nextPosition = $position->moveDirection($outputDirection);
                    if (
                        $matrix->hasPoint($nextPosition)
                        && !$matrix->getPoint($nextPosition)->hasVisitedInputDirection($outputDirection)
                    ) {
                        $nextRoundBeams[] = [$nextPosition, $outputDirection];
                    }
                }
            }
            $beams = $nextRoundBeams;
        }

        return count($matrix->where(fn (MirrorCell $cell) => $cell->isEnergized()));
    }

    /**
     * @return Matrix<MirrorCell>
     */
    private function parseInput(PuzzleInput $input): Matrix
    {
        return Matrix::read($input, fn (string $char) => new MirrorCell(Mirror::from($char)));
    }
}
