<?php

declare(strict_types=1);

namespace App\Model\Iterator;

use Countable;
use IteratorAggregate;

/**
 * @template TKey
 * @template TValue
 * @extends IteratorAggregate<TKey, TValue>
 */
interface IteratorInterface extends IteratorAggregate, Countable
{
    /**
     * @return array<TKey, TValue>
     */
    public function toArray(): array;

    /**
     * @param callable(TValue, TKey): bool $where
     * @return WhereIterator<TKey, TValue>
     */
    public function where(callable $where): WhereIterator;

    /**
     * @template TMappedValue
     * @param callable(TValue): TMappedValue $callback
     * @return MapIterator<TValue, TMappedValue>
     */
    public function map(callable $callback): MapIterator;

    /**
     * @return TValue
     */
    public function max(): mixed;

    /**
     * @param callable(TValue, TValue): TValue $selector
     * @return TValue
     */
    public function single(callable $selector): mixed;
}