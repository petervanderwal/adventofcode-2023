<?php

declare(strict_types=1);

namespace App\Model\Iterator;

/**
 * @template TKey
 * @template TValue
 */
abstract class AbstractIterator implements IteratorInterface
{
    public function count(): int
    {
        $result = 0;
        foreach ($this as $unused) {
            $result++;
        }
        return $result;
    }

    public function empty(): bool
    {
        foreach ($this as $unused) {
            return false;
        }
        return true;
    }

    public function toArray(): array
    {
        return iterator_to_array($this);
    }

    /**
     * @return ArrayIterator<TKey, TValue>
     */
    public function cacheIterator(): ArrayIterator
    {
        return new ArrayIterator($this->toArray());
    }

    /**
     * @return TValue
     */
    public function first(): mixed
    {
        foreach ($this as $item) {
            return $item;
        }
        return null;
    }

    /**
     * @return KeyIterator<TKey>
     */
    public function keys(): KeyIterator
    {
        return new KeyIterator($this);
    }

    /**
     * @return AbstractIterator<TKey, TValue>
     */
    public function reverse(): AbstractIterator
    {
        return new ArrayIterator(array_reverse(iterator_to_array($this)));
    }

    public function where(callable $where): WhereIterator
    {
        return new WhereIterator($this, $where);
    }

    /**
     * @param TValue $search
     * @return WhereIterator<TKey, TValue>
     */
    public function whereEquals(mixed $search): WhereIterator
    {
        return $this->where(fn (mixed $value) => $value === $search);
    }

    public function map(callable $callback): MapIterator
    {
        return new MapIterator($this, $callback);
    }

    /**
     * @param callable(TValue, TKey): void $callback
     * @return $this
     */
    public function each(callable $callback): static
    {
        foreach ($this as $key => $value) {
            $callback($value, $key);
        }
        return $this;
    }

    /**
     * @return MergeIterator<TKey, TValue>
     */
    public function merge(): MergeIterator
    {
        return new MergeIterator($this);
    }

    public function max(): mixed
    {
        return $this->single('max');
    }

    public function single(callable $selector): mixed
    {
        $result = null;
        foreach ($this as $item) {
            if ($result === null) {
                $result = $item;
                continue;
            }
            $result = $selector($result, $item);
        }
        return $result;
    }

    /**
     * @param callable(TValue): bool $selector
     */
    public function has(callable $selector): bool
    {
        foreach ($this as $item) {
            if ($selector($item)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param TValue $search
     */
    public function hasEquals(mixed $search): bool
    {
        return $this->has(fn (mixed $value) => $value === $search);
    }

    /**
     * @param callable(TValue): bool $selector
     * @return bool
     */
    public function all(callable $selector): bool
    {
        foreach ($this as $item) {
            if (!$selector($item)) {
                return false;
            }
        }
        return true;
    }

    public function sum(): int|float
    {
        return $this->reduce(fn (int|float $carry, int|float $item) => $carry + $item, 0);
    }

    public function implode(string $separator): string
    {
        return $this->reduce(
            fn (?string $carry, mixed $item) => $carry === null ? (string)$item : $carry . $separator . $item
        ) ?? '';
    }

    /**
     * @template TReducedType
     * @param callable(TReducedType $carry, TValue $item): TReducedType $callback
     * @param TReducedType|null $initial
     * @return TReducedType|null
     */
    public function reduce(callable $callback, mixed $initial = null): mixed
    {
        foreach ($this as $item) {
            $initial = $callback($initial, $item);
        }
        return $initial;
    }
}