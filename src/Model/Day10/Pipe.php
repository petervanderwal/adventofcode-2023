<?php

declare(strict_types=1);

namespace App\Model\Day10;

use App\Model\Direction;
use App\Model\Matrix;

enum Pipe: string
{
    case EMPTY = '.';
    case SQUEEZABLE = ' ';
    case START = 'S';
    case NORTH_SOUTH = '|';
    case EAST_WEST = '-';
    case NORTH_EAST = 'L';
    case NORTH_WEST = 'J';
    case SOUTH_WEST = '7';
    case SOUTH_EAST = 'F';

    public static function fromDirections(Direction $entranceDirection, Direction $exitDirection): self
    {
        foreach (self::cases() as $pipe) {
            $pipeDirections = $pipe->getDirections();
            if (
                count($pipeDirections) === 2
                && (
                    ($pipeDirections[0] === $entranceDirection && $pipeDirections[1] === $exitDirection)
                    || ($pipeDirections[1] === $entranceDirection && $pipeDirections[0] === $exitDirection)
                )
            ) {
                return $pipe;
            }
        }

        throw new \InvalidArgumentException('No such pipe with directions ' . $entranceDirection->name . ' and ' . $exitDirection->name, 231210111912);
    }

    /**
     * @return Direction[]
     */
    public function getDirections(): array
    {
        return match ($this) {
            self::EMPTY, self::SQUEEZABLE => [],
            self::START => Direction::straightCases(),
            self::NORTH_SOUTH => [Direction::NORTH, Direction::SOUTH],
            self::EAST_WEST => [Direction::EAST, Direction::WEST],
            self::NORTH_EAST => [Direction::NORTH, Direction::EAST],
            self::NORTH_WEST => [Direction::NORTH, Direction::WEST],
            self::SOUTH_WEST => [Direction::SOUTH, Direction::WEST],
            self::SOUTH_EAST => [Direction::SOUTH, Direction::EAST],
        };
    }

    public function getExitDirection(Direction $entranceDirection): ?Direction
    {
        $directions = $this->getDirections();

        /** @noinspection PhpDuplicateMatchArmBodyInspection The order of execution matters */
        return match (true) {
            count($directions) !== 2 => null,
            $entranceDirection === $directions[0] => $directions[1],
            $entranceDirection === $directions[1] => $directions[0],
            default => null
        };
    }

    public function blowUpByThree(): Matrix
    {
        static $cache = [];
        return new Matrix(...($cache[$this->value] ??= $this->buildBlowUpByThree()));
    }

    private function buildBlowUpByThree(): array
    {
        $directions = $this->getDirections();
        return [
            [self::SQUEEZABLE, in_array(Direction::NORTH, $directions) ? self::NORTH_SOUTH : self::SQUEEZABLE, self::SQUEEZABLE],
            [in_array(Direction::WEST, $directions) ? self::EAST_WEST : self::SQUEEZABLE, $this, in_array(Direction::EAST, $directions) ? self::EAST_WEST : self::SQUEEZABLE],
            [self::SQUEEZABLE, in_array(Direction::SOUTH, $directions) ? self::NORTH_SOUTH : self::SQUEEZABLE, self::SQUEEZABLE],
        ];
    }
}
