<?php

declare(strict_types=1);

namespace App\Model\Iterator;

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

    public function cacheIterator(): ArrayIterator
    {
        return new ArrayIterator($this->toArray());
    }

    public function first(): mixed
    {
        foreach ($this as $item) {
            return $item;
        }
        return null;
    }

    public function keys(): KeyIterator
    {
        return new KeyIterator($this);
    }

    public function where(callable $where): WhereIterator
    {
        return new WhereIterator($this, $where);
    }

    public function whereEquals(mixed $search): WhereIterator
    {
        return $this->where(fn (mixed $value) => $value === $search);
    }

    public function map(callable $callback): MapIterator
    {
        return new MapIterator($this, $callback);
    }

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

    public function has(callable $selector): bool
    {
        foreach ($this as $item) {
            if ($selector($item)) {
                return true;
            }
        }
        return false;
    }

    public function hasEquals(mixed $search): bool
    {
        return $this->has(fn (mixed $value) => $value === $search);
    }

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

    public function reduce(callable $callback, mixed $initial = null): mixed
    {
        foreach ($this as $item) {
            $initial = $callback($initial, $item);
        }
        return $initial;
    }
}