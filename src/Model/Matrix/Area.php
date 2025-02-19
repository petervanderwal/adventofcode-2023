<?php

declare(strict_types=1);

namespace App\Model\Matrix;

use App\Model\Iterator\AbstractArrayIterator;
use App\Model\Matrix;
use App\Model\Point;

class Area extends AbstractArrayIterator
{
    /** @var Point[] */
    private array $points;

    public function __construct(
        public readonly Matrix $matrix,
        Point ...$points
    ) {
        $this->points = $points;
    }

    /**
     * @return Point[]
     */
    public function getPoints(): array
    {
        return $this->points;
    }

    public function toArray(): array
    {
        return $this->points;
    }

    public function addPoint(Point $point): static
    {
        $this->points[] = $point;
        return $this;
    }

    public function isBorderArea(): bool
    {
        return $this->has(fn (Point $point) => $this->matrix->isBorderPoint($point));
    }

    public function getSize(): int
    {
        return count($this);
    }
}
