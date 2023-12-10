<?php

declare(strict_types=1);

namespace App\Puzzle;

use App\Model\Day10\Pipe;
use App\Model\Direction;
use App\Model\Matrix;
use App\Model\Point;
use App\Model\PuzzleInput;

class Day10 extends AbstractPuzzle
{
    public function calculateAssignment1(PuzzleInput $input): int
    {
        return (int)ceil($this->walkLoops($input) / 2);
    }

    public function calculateAssignment2(PuzzleInput $input): int
    {
        /** @var Matrix $pipeBorderMatrix */
        $pipeBorderMatrix = null;

        $onNewStartDirection = function (Matrix $matrix, Point $startPoint) use (&$pipeBorderMatrix) {
            $pipeBorderMatrix = Matrix::fill(
                $matrix->getNumberOfRows() * 3,
                $matrix->getNumberOfColumns() * 3,
                fn (int $row, int $column) => $row % 3 === 1 && $column % 3 === 1 ? Pipe::EMPTY : Pipe::SQUEEZABLE
            );
        };

        $onStep = function (Point $currentPosition, Pipe $pipe) use (&$pipeBorderMatrix) {
            $pipeBorderMatrix->setFromMatrix($pipe->blowUpByThree(), $currentPosition->multiply(3));
        };

        $onBackAtStart = function (Point $startPosition, Direction $startEntranceDirection, Direction $startExitDirection) use (&$pipeBorderMatrix) {
            $blownStartPosition = $startPosition->multiply(3);
            $pipeBorderMatrix->setFromMatrix(
                Pipe::fromDirections($startEntranceDirection->turnAround(), $startExitDirection)->blowUpByThree(),
                $blownStartPosition
            );
            $pipeBorderMatrix->setPoint($blownStartPosition->moveXY(1, 1), Pipe::START);
        };

        $this->walkLoops($input, $onNewStartDirection, $onStep, $onBackAtStart);

        $amountOfEmptyPlaces = count($pipeBorderMatrix->whereEquals(Pipe::EMPTY));

        $amountOfOutsideEmptyPlaces = count(
            $pipeBorderMatrix->getAreas(
                fn(Pipe $section) => $section === Pipe::EMPTY || $section === Pipe::SQUEEZABLE,
                fn(Pipe $section, Point $point) => $pipeBorderMatrix->isBorderPoint($point),
            )
                ->merge()
                ->where(fn (Point $point) => $pipeBorderMatrix->getPoint($point) === Pipe::EMPTY)
        );

        return $amountOfEmptyPlaces - $amountOfOutsideEmptyPlaces;
    }

    private function walkLoops(
        PuzzleInput $input,
        ?callable $onNewStartDirection = null,
        ?callable $onStep = null,
        ?callable $onBackAtStart = null,
    ): ?int {
        [$matrix, $startPoint] = $this->parseInput($input);
        foreach (Direction::straightCases() as $direction) {
            $startDirection = $direction;
            $currentPosition = $startPoint;
            if ($onNewStartDirection !== null) {
                $onNewStartDirection($matrix, $startPoint, $direction);
            }

            for ($step = 0;; $step++) {
                $currentPosition = $currentPosition->moveDirection($direction);
                if (!$matrix->hasPoint($currentPosition)) {
                    // Out of bounds
                    $direction = null;
                    break; // Try next direction from start point
                }

                /** @var Pipe $nextPipe */
                $nextPipe = $matrix->getPoint($currentPosition);
                if ($nextPipe === Pipe::START) {
                    if ($onBackAtStart !== null) {
                        $onBackAtStart($currentPosition, $direction, $startDirection, $step);
                    }
                    return $step;
                }
                $direction = $nextPipe->getExitDirection($direction->turnAround());
                if ($direction === null) {
                    // Pipe not connected
                    break; // Try next direction from start point
                }

                if ($onStep !== null) {
                    $onStep($currentPosition, $nextPipe, $direction);
                }
            }
        }
        return null;
    }

    /**

     * @return array{0: Matrix, 1: Point} Map and start point
     */
    private function parseInput(PuzzleInput $input): array
    {
        $matrix = Matrix::read($input, fn (string $char) => Pipe::from($char));
        $startPoint = $matrix->where(fn (Pipe $pipe) => $pipe === Pipe::START)->keys()->first();
        return [$matrix, $startPoint];
    }
}
