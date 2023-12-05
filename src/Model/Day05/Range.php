<?php

declare(strict_types=1);

namespace App\Model\Day05;

class Range
{
    public function __construct(
        public readonly int $destinationStart,
        public readonly int $sourceStart,
        public readonly int $length,
    ) {}

    public function solve(int $number): ?int
    {
        if ($number < $this->sourceStart || $number >= $this->sourceStart + $this->length) {
            return null;
        }
        return $this->destinationStart + $number - $this->sourceStart;
    }
}
