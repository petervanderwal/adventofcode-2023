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

    public function getDestinationBySource(int $source): ?int
    {
        if ($source < $this->sourceStart || $source >= $this->sourceStart + $this->length) {
            return null;
        }
        return $this->destinationStart + $source - $this->sourceStart;
    }

    public function getSourceByDestination(int $destination): ?int
    {
        if ($destination < $this->destinationStart || $destination >= $this->destinationStart + $this->length) {
            return null;
        }
        return $this->sourceStart + $destination - $this->destinationStart;
    }
}
