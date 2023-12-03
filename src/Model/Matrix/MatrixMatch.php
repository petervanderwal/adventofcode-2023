<?php

declare(strict_types=1);

namespace App\Model\Matrix;

use App\Model\Point;

class MatrixMatch
{
    /**
     * @param array<int|string, MatrixMatch|null> $groups
     */
    public function __construct(
        private readonly string $match,
        private readonly Point $startCoordinate,
        private readonly array $groups,
    ) {}

    public function getMatch(): string
    {
        return $this->match;
    }

    public function getStartCoordinate(): Point
    {
        return $this->startCoordinate;
    }

    public function getEndCoordinate(): Point
    {
        return new Point($this->startCoordinate->x + strlen($this->match) - 1, $this->startCoordinate->y);
    }

    /**
     * @return array<int|string, MatrixMatch|null>
     */
    public function getGroups(): array
    {
        return $this->groups;
    }
}
