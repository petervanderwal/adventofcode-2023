<?php

declare(strict_types=1);

namespace App\Model\Matrix;

use App\Model\Iterator\AbstractIterator;
use App\Model\Matrix;
use App\Model\Point;
use Traversable;

abstract class AbstractMatrixIterator extends AbstractIterator
{
    public function __construct(
        protected Matrix $matrix,
        protected bool $reverse = false,
    ) {}

    public function reverse(): static
    {
        return new static($this->matrix, !$this->reverse);
    }

    abstract protected function getItem(int $index): mixed;

    protected function getIndex(int $index): int|Point
    {
        return $index;
    }

    public function get(int $index): mixed
    {
        return $this->getItem($index);
    }

    public function getIterator(): Traversable
    {
        yield from $this->reverse ? $this->getReverseIterator() : $this->getForwardIterator();
    }

    protected function getForwardIterator(): Traversable
    {
        $count = count($this);
        for ($index = 0; $index < $count; $index++) {
            yield $this->getIndex($index) => $this->getItem($index);
        }
    }

    protected function getReverseIterator(): Traversable
    {
        $count = count($this);
        for ($index = $count - 1; $index >= 0; $index--) {
            yield $this->getIndex($index) => $this->getItem($index);
        }
    }
}