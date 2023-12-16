<?php

declare(strict_types=1);

namespace App\Model\Day12;

class Record
{
    /**
     * @var int[]
     */
    public readonly array $parts;
    public readonly int $totalLength;
    public readonly string $cacheKey;
    private Record $sliceOffFirstGroup;
    private array $eatUpFirstQuestionMarks = [];

    private function __construct(int ...$parts)
    {
        $this->parts = $parts;
        $this->totalLength = array_sum($this->parts);
        $this->cacheKey = implode(',', $this->parts);
    }

    public static function fromString(string $record): self
    {
        return new self(...array_map(
            strlen(...),
            preg_split('/(#+)/', $record, flags: PREG_SPLIT_DELIM_CAPTURE)
        ));
    }

    public function sliceOffFirstGroup(): Record
    {
        return $this->sliceOffFirstGroup ??= new Record(...array_slice($this->parts, 2));
    }

    public function eatUpFirstQuestionMarks(int $amount): self
    {
        if ($amount === 0) {
            return $this;
        }
        if ($amount > $this->parts[0]) {
            throw new \InvalidArgumentException('Can\t eat up ' . $amount . ' question marks from ' . $this->parts[0], 231226132806);
        }

        return $this->eatUpFirstQuestionMarks[$amount] ??= new self(
            $this->parts[0] - $amount,
            ...array_slice($this->parts, 1)
        );
    }
}