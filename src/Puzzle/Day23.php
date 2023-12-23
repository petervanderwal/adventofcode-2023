<?php

declare(strict_types=1);

namespace App\Puzzle;

use App\Algorithm\ShortestPath\Edge;
use App\Algorithm\ShortestPath\Graph;
use App\Model\Direction;
use App\Model\Matrix;
use App\Model\Point;
use App\Model\PuzzleInput;

class Day23 extends AbstractPuzzle
{
    protected function doCalculateAssignment1(PuzzleInput $input): int
    {
        return $this->calculateLongestScenicRoute($input);
    }

    protected function doCalculateAssignment2(PuzzleInput $input): int
    {
        return $this->calculateLongestScenicRoute(
            $input->replace('>', '.')
                ->replace('v', '.')
                ->replace('<', '.')
                ->replace('^', '.')
        );
    }

    private function calculateLongestScenicRoute(PuzzleInput $input): int
    {
        [$graph, $entryPoint, $exitPoint] = $this->getSimplifiedGraph($input);

        $pathsToExit = [];
        $pathsToInvestigate = [
            [
                'position' => $entryPoint,
                'path' => [
                    'cost' => 0,
                    'points' => [$entryPoint->getVertexIdentifier()],
                ]
            ]
        ];

        while (count($pathsToInvestigate)) {
            $newPathsToInvestigate = [];
            foreach ($pathsToInvestigate as ['position' => $from, 'path' => $path]) {
                /** @var Point $from */
                /** @var array{cost: int, points: string[]} $path */
                foreach ($graph->getEdges($from) as $edge) {
                    if (in_array($edge->getToVertex()->getVertexIdentifier(), $path['points'], true)) {
                        continue; // Don't revisit the same vertex twice
                    }

                    $newPath = [
                        'cost' => $path['cost'] + $edge->getEdgeCost(),
                        'points' => [...$path['points'], $edge->getToVertex()->getVertexIdentifier()],
                    ];
                    if ($edge->getToVertex()->getVertexIdentifier() === $exitPoint->getVertexIdentifier()) {
                        // Path to exit found
                        $pathsToExit[] = $newPath;
                        continue;
                    }

                    $newPathsToInvestigate[] = [
                        'position' => $edge->getToVertex(),
                        'path' => $newPath,
                    ];
                }
            }
            $pathsToInvestigate = $newPathsToInvestigate;
        }

        return max(array_map(fn (array $path) => $path['cost'], $pathsToExit));
    }

    /**
     * @param PuzzleInput $input
     * @return array{0: Graph, 1: Point, 2: Point}
     */
    private function getSimplifiedGraph(PuzzleInput $input): array
    {
        $map = Matrix::read($input);
        $mapEntryPoint = $this->getOpenPoint($map->getRow(0));
        $mapExitPoint = $this->getOpenPoint($map->getRow($map->getNumberOfRows() - 1));

        // Get simplified graph
        $edges = [];
        $deadEnds = [];
        $pointsToInvestigate = [[$mapEntryPoint, Direction::SOUTH]];
        while (count($pointsToInvestigate)) {
            $newPointsToInvestigate = [];

            foreach ($pointsToInvestigate as [$startPoint, $startDirection]) {
                $edgeId = $startPoint . '->' . $startDirection->name;
                if (isset($edges[$edgeId]) || in_array($edgeId, $deadEnds, true)) {
                    // Already investigated
                    continue;
                }

                /** @var Point $startPoint */
                /** @var Direction $startDirection */
                $toPoint = $startPoint->moveDirection($startDirection);
                $currentDirection = $startDirection;
                for ($length = 1; ; $length++) {
                    $reachableNeighbours = $this->getReachableNeighbours(
                        $map,
                        $toPoint,
                        notDirection: $currentDirection->turnAround()
                    );
                    if (count($reachableNeighbours) === 1) {
                        [$toPoint, $currentDirection] = $reachableNeighbours[0];
                        continue; // Next step
                    }

                    if (count($reachableNeighbours) === 0) {
                        $deadEnds = [$edgeId]; // Found dead end

                        if ($toPoint->equals($mapExitPoint)) {
                            // Found dead end to exit, add this as edge
                            $edges[$edgeId] = new Edge($startPoint, $toPoint, $length);
                        }

                        break;
                    }

                    // Found splitting point:
                    //  1. Add path to edges
                    $edges[$edgeId] = new Edge($startPoint, $toPoint, $length);
                    //  2. and investigate further from this splitting point
                    foreach ($this->getReachableNeighbours($map, $toPoint) as [, $direction]) {
                        $newPointsToInvestigate[] = [$toPoint, $direction];
                    }
                    break;
                }
            }

            $pointsToInvestigate = $newPointsToInvestigate;
        }

        $graph = new Graph();
        foreach ($edges as $edge) {
            $graph->addEdge($edge, true);
        }
        return [$graph, $mapEntryPoint, $mapExitPoint];
    }

    private function getOpenPoint(Matrix\Row $row): Point
    {
        return $row->whereEquals('.')->keys()->first();
    }

    /**
     * @return array<int, array{0: Point, 1: Direction}>
     */
    public function getReachableNeighbours(Matrix $map, Point $from, ?Direction $notDirection = null): array
    {
        $result = [];

        $directions = '.' !== ($character = $map->getPoint($from))
            ? [Direction::fromCharacter($character)]
            : Direction::straightCases();

        foreach ($directions as $direction) {
            $to = $from->moveDirection($direction);
            if (
                $direction === $notDirection
                || !$map->hasPoint($to)
                || $map->getPoint($to) === '#'
            ) {
                continue;
            }

            $result[] = [$to, $direction];
        }

        return $result;
    }
}
