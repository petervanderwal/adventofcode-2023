<?php

declare(strict_types=1);

namespace App\Model\Day19;

class ConditionSet
{
    /**
     * @var array<string, RangeCondition>
     */
    private array $conditions = [];

    public function and(Condition $condition): ?ConditionSet
    {
        $range = isset($this->conditions[$condition->testField])
            ? $this->conditions[$condition->testField]->and($condition)
            : $condition->toRange();

        if ($range === null) {
            // Not possible
            return null;
        }

        $result = new self();
        $result->conditions = $this->conditions;
        $result->conditions[$condition->testField] = $range;
        return $result;
    }

    public function __toString(): string
    {
        $result = [];
        foreach ($this->conditions as $field => $range) {
            $result[] = $range->toString($field);
        }
        return implode(', ', $result);
    }

    public function getSize(): int
    {
        $result = 1;
        foreach ($this->conditions as $condition) {
            $result *= $condition->getSize();
        }
        return $result;
    }
}
