<?php

declare(strict_types=1);

namespace App\Puzzle;

use App\Algorithm\Dijkstra;
use App\Model\Day17\GraphBuilder;
use App\Model\PuzzleInput;

class Day17 extends AbstractPuzzle
{
    protected function doCalculateAssignment1(PuzzleInput $input): int|string
    {
        return $this->calculateAssignment($input, 1, 3);
    }

    protected function doCalculateAssignment2(PuzzleInput $input): int|string
    {
        return $this->calculateAssignment($input, 4, 10);
    }

    private function calculateAssignment(PuzzleInput $input, int $minStepsInOneDirection, int $maxStepsInOneDirection): int
    {
        $graphBuilder = GraphBuilder::buildGraph($input, $minStepsInOneDirection, $maxStepsInOneDirection);
        return Dijkstra::calculate($graphBuilder->graph, $graphBuilder->startVertex)->getDistance($graphBuilder->destinationVertex);
    }
}
