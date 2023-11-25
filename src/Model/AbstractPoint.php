<?php

declare(strict_types=1);

namespace App\Model;

abstract class AbstractPoint
{
    abstract public function getX(): int;
    abstract public function getY(): int;

    public function getDistance(AbstractPoint $other): float
    {
        return sqrt(
            pow($this->getX() - $other->getX(), 2)
            + pow($this->getY() - $other->getY(), 2)
        );
    }

    public function getManhattanDistance(AbstractPoint $other): int
    {
        return abs($this->getX() - $other->getX()) + abs($this->getY() - $other->getY());
    }

    public function getXYString(): string
    {
        return sprintf('%d,%d', $this->getX(), $this->getY());
    }

    public function __toString(): string
    {
        return $this->getXYString();
    }
    
    public function getColumn(): int
    {
        return $this->getX();
    }

    public function getRow(): int
    {
        return $this->getY();
    }
}