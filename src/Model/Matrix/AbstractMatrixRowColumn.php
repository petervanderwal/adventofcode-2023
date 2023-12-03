<?php

declare(strict_types=1);

namespace App\Model\Matrix;

use App\Model\Matrix;
use App\Model\Point;

abstract class AbstractMatrixRowColumn extends AbstractMatrixIterator
{
    public function __construct(
        Matrix $matrix,
        protected int $index,
        bool $reverse = false,
    ) {
        parent::__construct($matrix, $reverse);
    }

    public function reverse(): static
    {
        return new static($this->matrix, $this->index, !$this->reverse);
    }

    abstract protected function getCoordinate(int $index): Point;

    protected function getIndex(int $index): int|Point
    {
        return $this->getCoordinate($index);
    }

    protected function getItem(int $index): mixed
    {
        return $this->matrix->getPoint($this->getCoordinate($index));
    }

    public function toString(?callable $characterPlotter = null): string
    {
        $result = '';
        foreach ($this as $character) {
            $result .= $characterPlotter === null ? $character : $characterPlotter($character);
        }
        return $result;
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
