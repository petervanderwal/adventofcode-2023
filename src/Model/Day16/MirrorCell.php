<?php

declare(strict_types=1);

namespace App\Model\Day16;

use App\Model\Direction;

class MirrorCell
{
    /**
     * @var array<string, bool>
     */
    private array $visitedInputDirections = [];

    public function __construct(
        public readonly Mirror $mirror,
    ) {}

    /**
     * @return Direction[] Output directions
     */
    public function beamIn(Direction $inputDirection): array
    {
        $this->visitedInputDirections[$inputDirection->name] = true;
        return $this->mirror->getBeamOutputDirections($inputDirection);
    }

    public function hasVisitedInputDirection(Direction $inputDirection): bool
    {
        return $this->visitedInputDirections[$inputDirection->name] ?? false;
    }

    public function isEnergized(): bool
    {
        return !empty($this->visitedInputDirections);
    }

    public function reset(): void
    {
        $this->visitedInputDirections = [];
    }

    public function __toString(): string
    {
        if ($this->mirror !== Mirror::EMPTY) {
            return $this->mirror->value;
        }

        $amountOfVisitedDirections = count($this->visitedInputDirections);
        if ($amountOfVisitedDirections === 0) {
            return '.';
        }
        if ($amountOfVisitedDirections > 1) {
            return (string)$amountOfVisitedDirections;
        }

        /** @var Direction $singleDirection */
        $singleDirection = (new \ReflectionEnum(Direction::class))->getCase(array_keys($this->visitedInputDirections)[0])->getValue();
        return $singleDirection->character();
    }
}
