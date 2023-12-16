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
    protected function doCalculateAssignment1(PuzzleInput $input): int|string
    {
        /** @var Matrix<MirrorCell> $matrix */
        $matrix = Matrix::read($input, fn (string $char) => new MirrorCell(Mirror::from($char)));

        $beams = [[$matrix->getCornerPoint(Direction::NORTH_WEST), Direction::EAST]];
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

        if ($input->isDemoInput()) {
            echo "\n\n" . $matrix->plot();
            echo "\n\n" . $matrix->plot(fn(MirrorCell $cell) => $cell->isEnergized() ? '#' : '.');
        }

        return count($matrix->where(fn (MirrorCell $cell) => $cell->isEnergized()));
    }

    protected function doCalculateAssignment2(PuzzleInput $input): int|string
    {
        // TODO: Implement calculateAssignment2() method.
    }
}
