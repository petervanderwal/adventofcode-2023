<?php

declare(strict_types=1);

namespace App\Model\Day12;

class Records
{
    /**
     * @var Record[]
     */
    public readonly array $records;
    public readonly string $cacheKey;
    private array $firstSlices = [];
    private array $offsetSlices = [];

    private function __construct(Record ...$records)
    {
        $this->records = $records;
        $this->cacheKey = implode('|', array_map(fn (Record $record) => $record->cacheKey, $this->records));
    }

    public static function fromString(string $record): self
    {
        return new self(...array_map(
            fn(string $part) => Record::fromString($part),
            preg_split('/\.+/', trim($record, '.'))
        ));
    }

    /**
     * @param positive-int $length
     */
    public function sliceFirst(int $length): Records
    {
        return $this->firstSlices[$length] ??= new self(...array_slice($this->records, 0, $length));
    }

    /**
     * @param positive-int $offset
     */
    public function sliceOffset(int $offset): Records
    {
        return $this->offsetSlices[$offset] ??= new self(...array_slice($this->records, $offset));
    }
}