<?php

declare(strict_types=1);

namespace App\Model\Iterator;

abstract class AbstractWrappedIterator extends AbstractIterator
{
    public function __construct(
        public readonly IteratorInterface $internalIterator,
    ) {}
}
