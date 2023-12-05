<?php

declare(strict_types=1);

namespace App\Model\Day05;

class RangeMap
{
    /**
     * @var Range[]
     */
    private array $ranges = [];

    public function append(Range $range): static
    {
        $this->ranges[] = $range;
        return $this;
    }

    public function solve(int $number): int
    {
        foreach ($this->ranges as $range) {
            if (null !== $result = $range->solve($number)) {
                return $result;
            }
        }
        // No translation
        return $number;
    }
}
