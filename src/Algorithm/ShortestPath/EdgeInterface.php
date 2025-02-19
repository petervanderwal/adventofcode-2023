<?php

declare(strict_types=1);

namespace App\Algorithm\ShortestPath;

interface EdgeInterface
{
    public function getFomVertex(): VertexInterface;
    public function getToVertex(): VertexInterface;
    public function getEdgeCost(): int;
}
