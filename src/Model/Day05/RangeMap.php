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

    public function getDestinationBySource(int $source): int
    {
        foreach ($this->ranges as $range) {
            if (null !== $result = $range->getDestinationBySource($source)) {
                return $result;
            }
        }
        // No translation
        return $source;
    }

    public function getSourceByDestination(int $destination): int
    {
        foreach ($this->ranges as $range) {
            if (null !== $result = $range->getSourceByDestination($destination)) {
                return $result;
            }
        }
        // No translation
        return $destination;
    }

    /**
     * @return Range[]
     */
    public function getRanges(): array
    {
        return $this->ranges;
    }
}
