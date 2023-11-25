<?php

declare(strict_types=1);

namespace App\Model;

class DirectedPoint extends Point
{
    public function __construct(
        public readonly Direction $direction,
        int $x = 0,
        int $y = 0,
    ) {
        parent::__construct($x, $y);
    }

    public function getNew(int $x, int $y, ?Direction $newDirection = null): static
    {
        return new static($newDirection ?? $this->direction, $x, $y);
    }

    public function turnRight(): static
    {
        return $this->getNew($this->x, $this->y, $this->direction->turnRight());
    }

    public function turnLeft(): static
    {
        return $this->getNew($this->x, $this->y, $this->direction->turnLeft());
    }

    public function turnAround(): static
    {
        return $this->getNew($this->x, $this->y, $this->direction->turnAround());
    }

    public function moveCurrentDirection(int $steps = 1): static
    {
        return $this->moveDirection($this->direction, $steps);
    }

    public function __toString(): string
    {
        return parent::__toString() . ' facing ' . $this->direction->prettyName();
    }
}
