<?php

declare(strict_types=1);

namespace App\Model\Day08;

class CycleData
{
    public function __construct(
        public readonly string $pathStart,
        public readonly array $zPositionsBeforeCycle,
        public readonly array $zPositionsWithinCycle,
        public readonly int $cycleStart,
        public readonly int $cycleLength,
    ) {}

    public function moveCycleStart(int $newCycleStart): CycleData
    {
        if ($newCycleStart === $this->cycleStart) {
            return $this;
        }
        if ($newCycleStart < $this->cycleStart) {
            throw new \InvalidArgumentException('Can\'t move cycleStart back in time', 231209215149);
        }

        $shiftLeft = $newCycleStart - $this->cycleStart;
        $cyclesToAdd = (int)ceil($shiftLeft) / $this->cycleLength;
        $newZPositionsBeforeCycle = $this->zPositionsBeforeCycle;
        for ($cycle = 1; $cycle <= $cyclesToAdd; $cycle++) {
            foreach ($this->getRealZPositionsFromCycle($cycle) as $zPosition) {
                if ($zPosition < $newCycleStart) {
                    $newZPositionsBeforeCycle[] = $zPosition;
                }
            }
        }

        $shiftRight = (int)(ceil($shiftLeft / $this->cycleLength) * $this->cycleLength) - $shiftLeft;
        $newZPositionsWithinCycle = array_map(
            fn (int $zPosition) => ($zPosition + $shiftRight) % $this->cycleLength,
            $this->zPositionsWithinCycle
        );

        return new CycleData(
            $this->pathStart,
            $newZPositionsBeforeCycle,
            $newZPositionsWithinCycle,
            $newCycleStart,
            $this->cycleLength,
        );
    }

    public function increaseCycleLength(int $newCycleLength): CycleData
    {
        if ($newCycleLength === $this->cycleLength) {
            return $this;
        }
        if ($newCycleLength % $this->cycleLength !== 0) {
            throw new \InvalidArgumentException(
                "Can't repeat a cycle of {$this->cycleLength} that many times so that a cycle of {$newCycleLength} is created",
                231209221610
            );
        }

        $repeats = (int)($newCycleLength / $this->cycleLength);
        $newZPositionsWithinCycle = $this->zPositionsWithinCycle;
        for ($repeat = 1; $repeat < $repeats; $repeat++) {
            $newZPositionsWithinCycle = [
                ...$newZPositionsWithinCycle,
                ...array_map(fn (int $position) => $position + $repeat * $this->cycleLength, $this->zPositionsWithinCycle)
            ];
        }

        return new CycleData(
            $this->pathStart,
            $this->zPositionsBeforeCycle,
            $newZPositionsWithinCycle,
            $this->cycleStart,
            $newCycleLength,
        );
    }

    /**
     * @return int[]
     */
    public function getRealZPositionsFromCycle(int $cycle): array
    {
        if ($cycle === 0) {
            return $this->zPositionsBeforeCycle;
        }
        return array_map(
            fn (int $position) => $this->cycleStart + ($cycle - 1) * $this->cycleLength + $position,
            $this->zPositionsWithinCycle
        );
    }

    public function getRealZPositionsFromFirstXCycles(int $amountOfCycles): array
    {
        return array_merge(
            ...array_map(fn (int $cycle) => $this->getRealZPositionsFromCycle($cycle), range(0, $amountOfCycles))
        );
    }
}