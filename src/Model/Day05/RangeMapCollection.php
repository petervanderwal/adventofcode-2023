<?php

declare(strict_types=1);

namespace App\Model\Day05;

class RangeMapCollection
{
    /**
     * @var RangeMap[]
     */
    private array $rangeMaps = [];

    public function startNewMap(): RangeMap
    {
        return $this->rangeMaps[] = new RangeMap();
    }

    public function solve(int $number)
    {
        foreach ($this->rangeMaps as $rangeMap) {
            $number = $rangeMap->getDestinationBySource($number);
        }
        return $number;
    }

    /**
     * @return int[]
     */
    public function getAllRangesSourceStartPoints(): array
    {
        $result = [];
        foreach (array_reverse($this->rangeMaps) as $rangeMap) {
            $result = array_map(
                fn (int $previousMapSourceThisMapDestination) => $rangeMap->getSourceByDestination($previousMapSourceThisMapDestination),
                $result
            );

            foreach ($rangeMap->getRanges() as $range) {
                $result[] = $range->sourceStart;
                $result[] = $range->sourceStart + $range->length; // At the end a new range starts
            }
        }

        $result = array_unique($result);
        sort($result);
        return $result;
    }
}
