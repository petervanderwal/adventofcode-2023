<?php

declare(strict_types=1);

namespace App\Model\Iterator;

use Traversable;

class WhereIterator extends AbstractIterator
{
    /**
     * @var callable
     */
    protected \Closure|string|array $where;

    public function __construct(
        protected AbstractIterator $iterator,
        callable $where
    ) {
        $this->where = $where;
    }

    public function getIterator(): Traversable
    {
        $where = $this->where;
        foreach ($this->iterator as $key => $item) {
            if ($where($item)) {
                yield $key => $item;
            }
        }
    }
}