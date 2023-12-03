<?php

declare(strict_types=1);

namespace App\Model;

use App\Algorithm\ShortestPath\VertexInterface;
use Symfony\Component\String\UnicodeString;

class Point extends AbstractPoint implements VertexInterface
{
    public function __construct(
        public readonly int $x,
        public readonly int $y,
    ) {}

    public static function fromString(string|UnicodeString $string, string $separator = ','): static
    {
        [$x, $y] = explode($separator, (string)$string);
        return new static((int)$x, (int)$y);
    }

    public function getX(): int
    {
        return $this->x;
    }

    public function getY(): int
    {
        return $this->y;
    }

    public function getVertexIdentifier(): string
    {
        return $this->getX() . ',' . $this->getY();
    }

    public function moveX(int $steps): static
    {
        return $this->moveXY($steps, 0);
    }

    public function moveY(int $steps): static
    {
        return $this->moveXY(0, $steps);
    }

    public function moveXY(int $xSteps, int $ySteps): static
    {
        return $this->getNew($this->x + $xSteps, $this->y + $ySteps);
    }

    public function offset(Point $offset): static
    {
        return $this->moveXY($offset->x, $offset->y);
    }

    protected function getNew(int $x, int $y): static
    {
        return new Point($x, $y);
    }

    public function moveDirection(Direction $direction, int $steps = 1): static
    {
        return $this->moveXY($direction->getXStep() * $steps, $direction->getYStep() * $steps);
    }

    public function isWithinAxis(int $maxX, int $maxY, int $minX = 0, int $minY = 0): bool
    {
        return $this->x >= $minX && $this->x <= $maxX && $this->y >= $minY && $this->y <= $maxY;
    }

    public function normalizeOnAxis(int $maxX, int $maxY, int $minX = 0, int $minY = 0): static
    {
        if ($this->isWithinAxis($maxX, $maxY, $minX, $minY)) {
            return $this;
        }
        return $this->getNew(
            self::normalizeOnSingleAxis($this->x, $minX, $maxX),
            self::normalizeOnSingleAxis($this->y, $minY, $maxY),
        );
    }

    private static function normalizeOnSingleAxis(int $current, int $min, int $max): int
    {
        $normalized = ($current - $min) % ($max - $min + 1);
        if ($normalized < 0) {
            $normalized += $max - $min + 1;
        }
        return $min + $normalized;
    }

    public function equals(Point $other): bool
    {
        return $this->x === $other->x && $this->y === $other->y;
    }
}