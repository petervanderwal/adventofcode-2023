<?php

declare(strict_types=1);

namespace App\Model\Day19;

use App\Model\Day05\Range;

class RangeCondition
{
    private function __construct(
        public readonly ?int $greaterThan,
        public readonly ?int $smallerThan,
    ) {
    }

    public static function greaterThan(int $greaterThan): self
    {
        return new self($greaterThan, null);
    }

    public static function smallerThan(int $smallerThan): self
    {
        return new self(null, $smallerThan);
    }

    public function and(RangeCondition|Condition $other): ?RangeCondition
    {
        if ($other instanceof Condition) {
            $other = $other->toRange();
        }

        $greaterThan = $this->greaterThan !== null && $other->greaterThan !== null
            ? max($this->greaterThan, $other->greaterThan)
            : ($this->greaterThan ?? $other->greaterThan);
        $smallerThan = $this->smallerThan !== null && $other->smallerThan !== null
            ? min($this->smallerThan, $other->smallerThan)
            : ($this->smallerThan ?? $other->smallerThan);

        if ($greaterThan !== null && $smallerThan !== null && $greaterThan >= $smallerThan) {
            // a>5 AND a<5  -- or --  a>6 AND a<4  -- both don't work well together
            return null;
        }

        return new RangeCondition($greaterThan, $smallerThan);
    }

    public function toString(string $testField): string
    {
        return match(null) {
            $this->greaterThan => $testField . '<' . $this->smallerThan,
            $this->smallerThan => $testField . '>' . $this->greaterThan,
            default => $this->greaterThan . '<' . $testField . '<' . $this->smallerThan,
        };
    }

    public function __toString(): string
    {
        return $this->toString('?');
    }

    public function getSize(): int
    {
        if ($this->greaterThan === null || $this->smallerThan === null) {
            throw new \BadMethodCallException('No size for this range', 231219224047);
        }
        return $this->smallerThan - $this->greaterThan - 1;
    }
}
