<?php

declare(strict_types=1);

namespace App\Model;

use App\Model\Iterator\AbstractIterator;
use App\Model\Iterator\GeneratedIterator;
use App\Model\Matrix\Area;
use App\Model\Matrix\Column;
use App\Model\Matrix\ColumnIterator;
use App\Model\Matrix\MatchIterator;
use App\Model\Matrix\Row;
use App\Model\Matrix\RowIterator;
use App\Utility\IterableUtility;
use Symfony\Component\String\UnicodeString;
use Traversable;

class Matrix extends AbstractIterator
{
    /**
     * @var array[]
     */
    private array $rows = [];
    private int $numberOfColumns;
    private Point $sectionOffset;

    public function __construct(array|Row ...$rows)
    {
        $this->addRows(...$rows);
    }

    public static function read(UnicodeString $string, ?callable $characterConverter = null): static
    {
        $rows = [];
        foreach ($string->split("\n") as $row) {
            $column = [];
            foreach (str_split(((string)$row)) as $character) {
                if ($characterConverter !== null) {
                    $character = $characterConverter($character, count($rows), count($column));
                }
                $column[] = $character;
            }
            $rows[] = $column;
        }
        return new static(...$rows);
    }

    public static function fill(int $numberOfRows, int $numberOfColumns, callable $initialValueGenerator): static
    {
        return (new static())->setNumberOfColumns($numberOfColumns)->addRowsFillWidth($numberOfRows, $initialValueGenerator);
    }

    public static function createFromPoints(
        callable $initialValueGenerator,
        callable $pointValueGenerator,
        Point ...$points
    ): static {
        if (empty($points)) {
            throw new \InvalidArgumentException('Points can\'t be empty', 231009195805);
        }

        $maxRow = null;
        $maxColumn = null;
        foreach ($points as $point) {
            if ($maxRow === null) {
                $maxRow = $point->getRow();
                $maxColumn = $point->getColumn();
            } else {
                $maxRow = max($maxRow, $point->getRow());
                $maxColumn = max($maxColumn, $point->getColumn());
            }
        }

        $matrix = static::fill($maxRow + 1, $maxColumn + 1, $initialValueGenerator);
        foreach ($points as $index => $point) {
            $matrix->setPoint($point, $pointValueGenerator($point, $index));
        }
        return $matrix;
    }

    public function getNumberOfRows(): int
    {
        return count($this->rows);
    }

    public function getNumberOfColumns(): int
    {
        return $this->numberOfColumns;
    }

    public function setNumberOfColumns(int $numberOfColumns): static
    {
        if (!empty($this->rows) && $numberOfColumns !== $this->numberOfColumns) {
            throw new \InvalidArgumentException('Can\'t overwrite number of columns when rows have been added already', 221217110335);
        }
        $this->numberOfColumns = $numberOfColumns;
        return $this;
    }

    public function addRow(mixed ...$columnValues): static
    {
        if (!isset($this->numberOfColumns)) {
            $this->numberOfColumns = count($columnValues);
        } elseif (count($columnValues) !== $this->numberOfColumns) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Amount of added %d columns should be of same width as matrix of %d',
                    count($columnValues),
                    $this->numberOfColumns
                ),
                221217105622
            );
        }

        $this->rows[] = $columnValues;
        return $this;
    }

    public function addRows(array|Row ...$rows): static
    {
        foreach ($rows as $row) {
            $this->addRow(...IterableUtility::removeKeys($row));
        }
        return $this;
    }

    public function addRowsFillWidth(int $numberOfRows, callable $initialValueGenerator): static
    {
        if (!isset($this->numberOfColumns)) {
            throw new \BadMethodCallException('Can\'t call ' . __METHOD__ . ' when the number of columns is not set', 221217110639);
        }

        for ($row = 0; $row < $numberOfRows; $row++) {
            $line = [];
            for ($column = 0; $column < $this->numberOfColumns; $column++) {
                $line[] = $initialValueGenerator($row, $column);
            }
            $this->addRow(...$line);
        }

        return $this;
    }

    public function hasCoordinate(int $rowIndex, int $columnIndex): bool
    {
        return array_key_exists($rowIndex, $this->rows) && array_key_exists($columnIndex, $this->rows[$rowIndex]);
    }

    public function hasPoint(Point $point): bool
    {
        return $this->hasCoordinate($point->getRow(), $point->getColumn());
    }

    public function get(int $rowIndex, int $columnIndex): mixed
    {
        if (!$this->hasCoordinate($rowIndex, $columnIndex)) {
            throw new \OutOfRangeException(sprintf('No such row, column: %d, %d', $rowIndex, $columnIndex), 221212071206);
        }
        return $this->rows[$rowIndex][$columnIndex];
    }

    public function getPoint(Point $point): mixed
    {
        return $this->get($point->getRow(), $point->getColumn());
    }

    public function getCornerPoint(Direction $direction): Point
    {
        return match($direction) {
            Direction::NORTH_WEST => new Point(0, 0),
            Direction::NORTH_EAST => new Point($this->getNumberOfColumns() - 1, 0),
            Direction::SOUTH_EAST => new Point($this->getNumberOfColumns() - 1, $this->getNumberOfRows() - 1),
            Direction::SOUTH_WEST => new Point(0, $this->getNumberOfRows() - 1),
            default => throw new \InvalidArgumentException('Only diagonal directions direct to a corner', 231014134419),
        };
    }

    public function isBorderPoint(Point $point): bool
    {
        return $point->x === 0 || $point->y === 0
            || $point->x === $this->getNumberOfColumns() - 1
            || $point->y === $this->getNumberOfRows() - 1;
    }

    public function set(int $rowIndex, int $columnIndex, mixed $value): static
    {
        if (!$this->hasCoordinate($rowIndex, $columnIndex)) {
            throw new \OutOfRangeException(sprintf('No such row, column: %d, %d', $rowIndex, $columnIndex), 221214072615);
        }
        $this->rows[$rowIndex][$columnIndex] = $value;
        return $this;
    }

    public function setPoint(Point $point, mixed $value): static
    {
        return $this->set($point->getRow(), $point->getColumn(), $value);
    }

    public function setFromMatrix(Matrix $matrix, Point $offset = new Point(0, 0)): static
    {
        foreach ($matrix as $point => $value) {
            /** @var Point $point */
            $this->setPoint($point->offset($offset), $value);
        }
        return $this;
    }
    
    public function drawPath(Point $start, Point $destination, callable $valueGenerator): static
    {
        if ($destination->getColumn() === $start->getColumn()) {
            for (
                $row = min($start->getRow(), $destination->getRow());
                $row <= max($start->getRow(), $destination->getRow());
                $row++
            ) {
                $this->set($row, $start->getColumn(), $valueGenerator($row, $start->getColumn()));
            }
            return $this;
        }
        
        if ($destination->getRow() === $start->getRow()) {
            for (
                $column = min($start->getColumn(), $destination->getColumn());
                $column <= max($start->getColumn(), $destination->getColumn());
                $column++
            ) {
                $this->set($start->getRow(), $column, $valueGenerator($start->getRow(), $column));
            }
            return $this;
        }

        throw new \InvalidArgumentException(
            sprintf(
                'Can\t draw diagonal path from %s to %s - only horizontal and vertical paths are supported',
                $start,
                $destination
            ),
            221214073708
        );
    }

    public function getRows(): RowIterator
    {
        return new RowIterator($this);
    }

    public function getRow(int $index): Row
    {
        if ($index < 0 || $index > $this->getNumberOfRows()) {
            throw new \OutOfRangeException('$index should be >= 0 and <= $matrix->getNumberOfRows()', 230921192835);
        }
        return new Row($this, $index);
    }

    public function getColumns(): ColumnIterator
    {
        return new ColumnIterator($this);
    }

    public function getColumn(int $index): Column
    {
        if ($index < 0 || $index > $this->getNumberOfColumns()) {
            throw new \OutOfRangeException('$index should be >= 0 and <= $matrix->getNumberOfColumns()', 230921191903);
        }
        return new Column($this, $index);
    }

    public function count(): int
    {
        return $this->getNumberOfRows() * $this->getNumberOfColumns();
    }

    /**
     * @return array<Point, mixed>|Traversable
     */
    public function getIterator(): Traversable
    {
        foreach ($this->getRows() as $row) {
            foreach ($row as $point => $item) {
                yield $point => $item;
            }
        }
    }

    public function matches(string $pattern): MatchIterator
    {
        return new MatchIterator($this, $pattern);
    }

    public function plot(?callable $characterPlotter = null): string
    {
        $lines = [];
        foreach ($this->getRows() as $row) {
            $lines[] = $row->toString($characterPlotter);
        }
        return implode(PHP_EOL, $lines);
    }

    public function extractSection(int $rowStart, int $columnStart, int $rowEnd, int $columnEnd): static
    {
        if (!$this->hasCoordinate($rowStart, $columnStart)) {
            throw new \OutOfRangeException(
                sprintf('No such start row, column: %d, %d', $rowStart, $columnStart),
                221214070727
            );
        }
        if (!$this->hasCoordinate($rowEnd, $columnEnd)) {
            throw new \OutOfRangeException(
                sprintf('No such end row, column: %d, %d', $rowEnd, $columnEnd),
                221214070757
            );
        }
        if ($rowEnd < $rowStart) {
            throw new \InvalidArgumentException('$rowEnd should be => $rowStart', 221214070918);
        }
        if ($columnEnd < $columnStart) {
            throw new \InvalidArgumentException('$columnEnd should be => $columnStart', 221214070948);
        }

        $lines = [];
        for ($row = $rowStart; $row <= $rowEnd; $row++) {
            $line = [];
            for ($column = $columnStart; $column <= $columnEnd; $column++) {
                $line[] = $this->rows[$row][$column];
            }
            $lines[] = $line;
        }

        $section = new static(...$lines);
        $section->sectionOffset = $this->getSectionOffset()->moveXY($columnStart, $rowStart);
        return $section;
    }

    /**
     * @return Point
     */
    public function getSectionOffset(): Point
    {
        if (!isset($this->sectionOffset)) {
            return new Point(0, 0);
        }
        return $this->sectionOffset;
    }

    /**
     * @param callable $belongsToArea Method to define whether a given neighbouring point belongs to an area, signature
     *                   fn (mixed $pointValue, Point $coordinate, Area $area)
     * @param callable|null $canBeStartOfNewArea Method to define whether a given point can be a start of a new area,
     *                   signature fn (mixed $pointValue, Point $coordinate, Matrix $matrix)
     * @return Matrix\Area[]|GeneratedIterator
     */
    public function getAreas(callable $belongsToArea, callable $canBeStartOfNewArea = null): GeneratedIterator
    {
        if ($canBeStartOfNewArea === null) {
            $canBeStartOfNewArea = fn () => true;
        }

        return new GeneratedIterator(function () use ($belongsToArea, $canBeStartOfNewArea) {
            $visited = Matrix::fill($this->getNumberOfRows(), $this->getNumberOfColumns(), fn() => false);
            foreach ($this as $point => $value) {
                /** @var Point $point */
                if ($visited->getPoint($point) || !$canBeStartOfNewArea($value, $point, $this)) {
                    continue;
                }

                yield $this->populateArea($point, $belongsToArea, $visited);
            }
        });
    }

    private function populateArea(Point $startingPoint, callable $belongsToArea, Matrix $visited): Area
    {
        $area = new Area($this, $startingPoint);
        $visited->setPoint($startingPoint, true);

        $pointsToCheck = [];
        do {
            foreach (Direction::straightCases() as $direction) {
                $neighbour = $startingPoint->moveDirection($direction);
                if (
                    $this->hasPoint($neighbour)
                    && !$visited->getPoint($neighbour)
                    && $belongsToArea($this->getPoint($neighbour), $neighbour, $area)
                ) {
                    $area->addPoint($neighbour);
                    $visited->setPoint($neighbour, true);
                    $pointsToCheck[] = $neighbour;
                }
            }
        } while (null !== $startingPoint = array_pop($pointsToCheck));

        return $area;
    }
}
