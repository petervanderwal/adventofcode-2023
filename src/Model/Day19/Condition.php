<?php

declare(strict_types=1);

namespace App\Model\Day19;

class Condition
{
    public function __construct(
        public readonly string $testField,
        public readonly string $condition,
        public readonly int $comparedValue,
    ) {}

    public static function fromString(string $string): self
    {
        return new self(
            $string[0],
            $string[1],
            (int)substr($string, 2)
        );
    }

    public function matches(array $part): bool
    {
        $value = $part[$this->testField];
        return match($this->condition) {
            '>' => $value > $this->comparedValue,
            '<' => $value < $this->comparedValue
        };
    }

    public function not(): Condition
    {
        // !(x > 5) === x <= 5 === x < 6
        // !(x < 5) === x >= 5 === x > 4

        return match($this->condition) {
            '>' => new self($this->testField, '<', $this->comparedValue + 1),
            '<' => new self($this->testField, '>', $this->comparedValue - 1),
        };
    }

    public function toRange(): RangeCondition
    {
        return match($this->condition) {
            '>' => RangeCondition::greaterThan($this->comparedValue),
            '<' => RangeCondition::smallerThan($this->comparedValue),
        };
    }
}
