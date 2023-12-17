<?php

declare(strict_types=1);

namespace App\Model\Day17;

use App\Algorithm\ShortestPath\VertexInterface;
use App\Model\Point;

class AxisPoint extends Point
{
    public function __construct(
        public readonly Point $point,
        public readonly ?Axis $axis = null,
    ) {}

    public function getVertexIdentifier(): string
    {
        return $this->axis?->value . $this->point;
    }
}
