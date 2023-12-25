<?php

declare(strict_types=1);

namespace App\Puzzle;

use App\Algorithm\Dijkstra;
use App\Algorithm\ShortestPath\Graph;
use App\Algorithm\ShortestPath\Vertex;
use App\Algorithm\ShortestPath\VertexInterface;
use App\Model\Day25\Edge;
use App\Model\PuzzleInput;
use App\Utility\ArrayUtility;

class Day25 extends AbstractPuzzle
{
    protected function doCalculateAssignment1(PuzzleInput $input): int|string
    {
        $graph = $this->getGraph($input);

        $edgeScores = $this->getEdgeScoresFromGraph($graph);
        $seenCombinations = [];
        foreach ($this->progressService->iterateWithProgressBar($this->iterateSteps()) as $unused) {
            $combination = $this->getBestCombinationWithThreeEdges($edgeScores, $seenCombinations);
            $seenCombinations[] = $combination;

            $answer = $this->withEdgesRemoved(
                $graph,
                function (Graph $graph) use (&$edgeScores): ?int {
                    $islands = $this->getIslandSizes($graph);
                    if (count($islands) === 2) {
                        return $islands[0] * $islands[1];
                    }

                    foreach ($this->getEdgeScoresFromGraph($graph) as $edgeId => $score) {
                        $edgeScores[$edgeId] = max($edgeScores[$edgeId] ?? 0, $score);
                    }
                    return null;
                },
                ...$combination
            );

            if ($answer !== null) {
                return $answer;
            }
        }

        throw new \UnexpectedValueException('No combination of 3 bridges found', 231225125702);
    }

    private function getGraph(PuzzleInput $input): Graph
    {
        $graph = new Graph();

        foreach ($input->split("\n") as $line) {
            [$from, $to] = $line->split(': ');
            if ($graph->hasVertex((string)$from)) {
                $from = $graph->getVertex((string)$from);
            } else {
                $graph->addVertex($from = new Vertex((string)$from));
            }

            foreach ($to->split(' ') as $to) {
                if ($graph->hasVertex((string)$to)) {
                    $to = $graph->getVertex((string)$to);
                } else {
                    $graph->addVertex($to = new Vertex((string)$to));
                }

                $graph->addEdge(new Edge($from, $to))->addEdge(new Edge($to, $from));
            }
        }

        return $graph;
    }

    private function getBestCombinationWithThreeEdges(array $edgeScores, array $seen = []): array
    {
        arsort($edgeScores);
        for ($top = 3; $top <= count($edgeScores); $top++) {
            foreach (ArrayUtility::getCombinations(3, ...array_slice(array_keys($edgeScores), 0, $top)) as $option) {
                if (!in_array($option, $seen, true)) {
                    return $option;
                }
            }
        }
    }

    /**
     * @return array<string, int> Edge scores with the edge id on the key, the score on the value
     */
    private function getEdgeScoresFromGraph(Graph $graph): array
    {
        // Calculate edge scores from the furthest point in the graph
        $shortestPaths = Dijkstra::calculate($graph, $this->getFurthestVertexFromGraph($graph));
        $edgeScores = $this->getEdgeScoresFromDijkstra($shortestPaths);
        // Increase with the calculation based on the opposite path (highly increasing the edge scores of those 3 bridges)
        foreach (
            $this->getEdgeScoresFromDijkstra(
                Dijkstra::calculate($graph, $this->getFurthestPointFromDijkstra($shortestPaths))
            ) as $edgeId => $score
        ) {
            $edgeScores[$edgeId] = ($edgeScores[$edgeId] ?? 0) + $score;
        }
        return $edgeScores;
    }

    private function getFurthestVertexFromGraph(Graph $graph): VertexInterface
    {
        // Get the furthest point from a random point in the graph, that will give a point deep in the graph
        $dijkstra = Dijkstra::calculate($graph, ArrayUtility::first($graph->getVertices()));
        return $this->getFurthestPointFromDijkstra($dijkstra);
    }

    private function getFurthestPointFromDijkstra(Dijkstra $shortestPaths): VertexInterface
    {
        $best = null;
        foreach ($shortestPaths->getGraph()->getVertices() as $vertex) {
            $distance = $shortestPaths->getDistance($vertex);
            if ($best === null || $distance > $best[1]) {
                $best = [$vertex, $distance];
            }
        }
        return $best[0];
    }

    /**
     * @return array<string, int> Edge scores with the edge id on the key, the score on the value
     */
    private function getEdgeScoresFromDijkstra(Dijkstra $shortestPaths): array
    {
        $graph = $shortestPaths->getGraph();
        $edgeScores = [];
        foreach ($graph->getVertices() as $vertex) {
            $stepFrom = null;
            foreach ($shortestPaths->getPath($vertex) as $stepNumber => $stepTo) {
                if ($stepFrom !== null) {
                    $edgeId = Edge::generateId($stepFrom, $stepTo);
                    // Edge score += position in path (= weight, so edges that occur farther away have a higher weight)
                    $edgeScores[$edgeId] = ($edgeScores[$edgeId] ?? 0) + $stepNumber;
                }
                $stepFrom = $stepTo;
            }
        }
        return $edgeScores;
    }

    /**
     * @template T
     * @param callable(Graph): T $do
     * @return T
     */
    private function withEdgesRemoved(Graph $graph, callable $do, string ...$edgesToRemove): mixed
    {
        $removedEdges = [];

        try {
            foreach ($edgesToRemove as $edge) {
                [$from, $to] = Edge::getVertexIdsFromEdgeId($edge);
                $removedEdges[] = $graph->removeEdgeByVertices($from, $to);
                $removedEdges[] = $graph->removeEdgeByVertices($to, $from);
            }

            return $do($graph);
        } finally {
            foreach ($removedEdges as $edge) {
                $graph->addEdge($edge);
            }
        }
    }

    /**
     * @return int[]
     */
    private function getIslandSizes(Graph $graph): array
    {
        $verticesOpen = array_map(fn (VertexInterface $vertex) => $vertex->getVertexIdentifier(), $graph->getVertices());
        $islands = [];

        while (count($verticesOpen)) {
            $islandSize = 0;
            $dijkstra = Dijkstra::calculate($graph, $graph->getVertex(ArrayUtility::first($verticesOpen)));
            foreach ($dijkstra->getAllDistances() as $to => $distance) {
                if ($distance === PHP_INT_MAX) {
                    continue;
                }
                $islandSize++;
                $verticesOpen = array_diff($verticesOpen, [$to]);
            }
            $islands[] = $islandSize;
        }

        return $islands;
    }

    protected function doCalculateAssignment2(PuzzleInput $input): int|string
    {
    }
}
