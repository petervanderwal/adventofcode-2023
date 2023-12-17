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
        $graphBuilder = GraphBuilder::buildGraph($input);
        return Dijkstra::calculate($graphBuilder->graph, $graphBuilder->startVertex)->getDistance($graphBuilder->destinationVertex);
    }

    protected function doCalculateAssignment2(PuzzleInput $input): int|string
    {
        // TODO: Implement calculateAssignment2() method.
    }
}
