<?php

declare(strict_types=1);

namespace App\Algorithm;

use App\Algorithm\ShortestPath\Graph;
use App\Algorithm\ShortestPath\GraphInterface;
use App\Algorithm\ShortestPath\VertexInterface;
use App\Model\WeightedQueue;

class Dijkstra
{
    /**
     * @var int[]
     */
    private array $distances;

    /**
     * @var VertexInterface[]
     */
    private array $previous;

    /** @var string[] */
    private array $sources;

    private WeightedQueue $queue;

    private function __construct(
        private GraphInterface $graph,
        string|VertexInterface ...$sources,
    ) {
        if (empty($sources)) {
            throw new \InvalidArgumentException('Sources can\'t be empty', 221217205409);
        }
        $this->sources = array_map(
            fn (string|VertexInterface $source) => $source instanceof VertexInterface ? $source->getVertexIdentifier() : $source,
            $sources
        );
        $this->queue = new WeightedQueue();
    }

    public static function calculate(GraphInterface $graph, VertexInterface ...$sources): static
    {
        return (new static($graph, ...$sources))
            ->initiate()
            ->run();
    }

    public function getGraph(): Graph
    {
        return $this->graph;
    }

    public function getAllDistances(): array
    {
        return $this->distances;
    }

    public function getDistance(string|VertexInterface $to): int
    {
        if ($to instanceof VertexInterface) {
            $to = $to->getVertexIdentifier();
        }
        return $this->distances[$to];
    }

    public function getAllPrevious(): array
    {
        return $this->previous;
    }

    public function getPrevious(string|VertexInterface $to): VertexInterface
    {
        if ($to instanceof VertexInterface) {
            $to = $to->getVertexIdentifier();
        }
        return $this->previous[$to];
    }

    /**
     * @param VertexInterface $to
     * @return VertexInterface[]
     */
    public function getPath(VertexInterface $to): array
    {
        $result = [$to];
        while (!in_array($to->getVertexIdentifier(), $this->sources, true)) {
            $result[] = $to = $this->getPrevious($to);
        }
        return array_reverse($result);
    }

    private function initiate(): static
    {
        $sourceFound = false;

        foreach ($this->graph->getVertices() as $vertex) {
            $isSource = in_array($vertex->getVertexIdentifier(), $this->sources, true);
            $sourceFound = $sourceFound || $isSource;
            $this->distances[$vertex->getVertexIdentifier()] = $isSource ? 0 : PHP_INT_MAX;
            $this->previous[$vertex->getVertexIdentifier()] = null;
            $this->queue->addWithPriority($vertex, $this->distances[$vertex->getVertexIdentifier()]);
        }

        if (!$sourceFound) {
            throw new \UnexpectedValueException('Shortest path source not found in graph vertices', 221217203455);
        }

        return $this;
    }

    private function run(): static
    {
        /** @var VertexInterface $from */
        foreach ($this->queue as $from) {
            foreach ($this->graph->getEdges($from) as $edge) {
                $to = $edge->getToVertex();
                if ($to->getVertexIdentifier() === $from->getVertexIdentifier()) {
                    continue;
                }

                $edgeCost = $edge->getEdgeCost();
                if ($edgeCost < 1) {
                    throw new \UnexpectedValueException('Each edge is expected to have a cost >= 1', 221217201624);
                }

                $pathCost = $this->distances[$from->getVertexIdentifier()] + $edgeCost;
                if ($pathCost < $this->distances[$to->getVertexIdentifier()]) {
                    // Shorter path found
                    $this->distances[$to->getVertexIdentifier()] = $pathCost;
                    $this->previous[$to->getVertexIdentifier()] = $from;
                    $this->queue->addWithPriority($to, $pathCost);
                }
            }
        }

        return $this;
    }
}
