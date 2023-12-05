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
            $number = $rangeMap->solve($number);
        }
        return $number;
    }
}
