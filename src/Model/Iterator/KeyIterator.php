<?php

declare(strict_types=1);

namespace App\Model\Iterator;

use Traversable;

class KeyIterator extends AbstractIterator
{
    public function __construct(
        protected AbstractIterator $iterator,
    ) {}

    public function getIterator(): Traversable
    {
        foreach ($this->iterator as $key => $item) {
            yield $key;
        }
    }
}
