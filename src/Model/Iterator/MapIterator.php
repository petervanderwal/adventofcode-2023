<?php

declare(strict_types=1);

namespace App\Model\Iterator;

use Traversable;

/**
 * @template TIteratorValue
 * @template TMappedValue
 * @extends AbstractIterator<int, TMappedValue>
 */
class MapIterator extends AbstractIterator
{
    /**
     * @var callable(TIteratorValue): TMappedValue
     */
    protected \Closure|string|array $callback;

    /**
     * @param AbstractIterator<mixed, TIteratorValue> $iterator
     * @param callable(TIteratorValue): TMappedValue $callback
     */
    public function __construct(
        protected AbstractIterator $iterator,
        callable $callback
    ) {
        $this->callback = $callback;
    }

    public function getIterator(): Traversable
    {
        $callback = $this->callback;
        foreach ($this->iterator as $item) {
            yield $callback($item);
        }
    }
}