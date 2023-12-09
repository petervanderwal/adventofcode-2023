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

    public function keys(): KeyIterator
    {
        return new KeyIterator($this);
    }

    public function where(callable $where): WhereIterator
    {
        return new WhereIterator($this, $where);
    }

    public function map(callable $callback): MapIterator
    {
        return new MapIterator($this, $callback);
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