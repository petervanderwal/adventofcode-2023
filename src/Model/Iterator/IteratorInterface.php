<?php

declare(strict_types=1);

namespace App\Model\Iterator;

use Countable;
use IteratorAggregate;

interface IteratorInterface extends IteratorAggregate, Countable
{
    public function toArray(): array;

    public function where(callable $where): WhereIterator;

    public function map(callable $callback): MapIterator;

    public function max(): mixed;

    public function single(callable $selector): mixed;
}