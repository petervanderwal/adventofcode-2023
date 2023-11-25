<?php

declare(strict_types=1);

namespace App\Model\Iterator;

use Traversable;

class MapIterator extends AbstractIterator
{
    /**
     * @var callable
     */
    protected \Closure|string|array $callback;

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