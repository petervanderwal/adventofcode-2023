<?php

declare(strict_types=1);

namespace App\Model\Matrix;

use App\Algorithm\ShortestPath\Edge;
use App\Algorithm\ShortestPath\Graph;
use App\Model\Direction;
use App\Model\Matrix;
use App\Model\Point;

class GraphBuilder
{
    private Graph $graph;
    /**
     * @var callable
     */
    private $edgeBuilder;

    /**
     * @param callable $edgeBuilder Callback to determine the edge or edge cost with signature
     *              fn (Point $from, mixed $fromValue, Point $to, mixed $toValue, Direction $direction): Edge|int|null
     *              If the callback returns null, the edge won't be added
     *              If the callback returns an integer, this will be treated as the edge cost
     */
    public function __construct(
        private Matrix $matrix,
        callable $edgeBuilder,
        private bool $allowDiagonalSteps = false,
    ) {
        $this->edgeBuilder = $edgeBuilder;
    }

    public function buildGraph(): Graph
    {
        $this->graph = new Graph();

        foreach ($this->matrix as $point => $value) {
            $this->addPointToGraph($point, $value);
        }

        return $this->graph;
    }

    private function addPointToGraph(Point $point, mixed $value): void
    {
        $directions = Direction::straightCases();
        if ($this->allowDiagonalSteps) {
            $directions = [...$directions, ...Direction::diagonalCases()];
        }
        foreach ($directions as $direction) {
            $this->addEdgeToGraph($point, $value, $point->moveDirection($direction), $direction);
        }
    }

    private function addEdgeToGraph(Point $from, mixed $fromValue, Point $to, Direction $direction): void
    {
        if (!$this->matrix->hasPoint($to)) {
            return;
        }

        $toValue = $this->matrix->getPoint($to);
        $edge = ($this->edgeBuilder)($from, $fromValue, $to, $toValue, $direction);
        if ($edge === null) {
            return;
        }
        if (is_int($edge)) {
            $edge = new Edge($from, $to, $edge);
        }
        $this->graph->addEdge($edge, true);
    }
}
