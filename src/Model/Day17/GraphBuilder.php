<?php

declare(strict_types=1);

namespace App\Model\Day17;

use App\Algorithm\ShortestPath\Edge;
use App\Algorithm\ShortestPath\Graph;
use App\Model\Direction;
use App\Model\Matrix;
use App\Model\Point;
use App\Model\PuzzleInput;

class GraphBuilder
{
    public readonly AxisPoint $startVertex;
    public readonly AxisPoint $destinationVertex;
    public readonly Graph $graph;

    public static function buildGraph(
        PuzzleInput $input,
        int $minStepsInOneDirection,
        int $maxStepsInOneDirection,
    ): GraphBuilder {
        return (new self(
            Matrix::read($input, fn (string $char) => (int)$char),
            $minStepsInOneDirection,
            $maxStepsInOneDirection,
        ))
            ->addEdges();
    }

    private function __construct(
        private readonly Matrix $matrix,
        private readonly int $minStepsInOneDirection,
        private readonly int $maxStepsInOneDirection,
    ) {
        $this->startVertex = new AxisPoint($this->matrix->getCornerPoint(Direction::NORTH_WEST));
        $this->destinationVertex = new AxisPoint($this->matrix->getCornerPoint(Direction::SOUTH_EAST));
        $this->graph = new Graph([$this->startVertex, $this->destinationVertex]);
    }

    private function addEdges(): self
    {
        foreach ($this->matrix->keys() as $point) {
            /** @var Point $point */
            if ($point->equals($this->destinationVertex->point)) {
                continue;
            }

            foreach (Direction::straightCases() as $direction) {
                $from = $point->equals($this->startVertex->point)
                    ? $this->startVertex
                    // We can move to south from any horizontal entry (and so on)
                    : new AxisPoint($point, Axis::fromDirection($direction)->other());

                $this->addStepsToGraph($from, $direction);
            }
        }
        return $this;
    }

    private function addStepsToGraph(AxisPoint $from, Direction $direction): void
    {
        $heatScore = 0;
        $axis = Axis::fromDirection($direction);

        for ($steps = 1; $steps <= $this->maxStepsInOneDirection; $steps++) {
            $toPoint = $from->point->moveDirection($direction, $steps);
            if (!$this->matrix->hasPoint($toPoint)) {
                break;
            }
            $heatScore += $this->matrix->getPoint($toPoint);

            if ($steps >= $this->minStepsInOneDirection) {
                $to = $toPoint->equals($this->destinationVertex->point)
                    ? $this->destinationVertex
                    : new AxisPoint($toPoint, $axis);
                $this->graph->addEdge(
                    new Edge($from, $to, $heatScore),
                    true
                );
            }
        }
    }
}
