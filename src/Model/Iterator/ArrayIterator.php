<?php

declare(strict_types=1);

namespace App\Model\Iterator;

class ArrayIterator extends AbstractArrayIterator
{
    public function __construct(
        private readonly array $data,
    ) {}

    public function toArray(): array
    {
        return $this->data;
    }
}
