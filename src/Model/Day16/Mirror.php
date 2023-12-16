<?php

declare(strict_types=1);

namespace App\Model\Day16;

use App\Model\Direction;

enum Mirror: string
{
    case EMPTY = '.';
    case MIRROR_NORTH_EAST = '/';
    case MIRROR_NORTH_WEST = '\\';
    case SPLITTER_VERTICAL = '|';
    case SPLITTER_HORIZONTAL = '-';

    /**
     * @return Direction[]
     */
    public function getBeamOutputDirections(Direction $inputDirection): array
    {
        /** @noinspection PhpUncoveredEnumCasesInspection */
        return match ($this) {
            self::EMPTY => [$inputDirection],
            self::MIRROR_NORTH_WEST => match ($inputDirection) {
                Direction::NORTH => [Direction::WEST],
                Direction::EAST => [Direction::SOUTH],
                Direction::SOUTH => [Direction::EAST],
                Direction::WEST => [Direction::NORTH],
            },
            self::MIRROR_NORTH_EAST => match ($inputDirection) {
                Direction::NORTH => [Direction::EAST],
                Direction::EAST => [Direction::NORTH],
                Direction::SOUTH => [Direction::WEST],
                Direction::WEST => [Direction::SOUTH],
            },
            self::SPLITTER_VERTICAL => match ($inputDirection) {
                Direction::NORTH, Direction::SOUTH => [$inputDirection],
                Direction::EAST, Direction::WEST => [Direction::NORTH, Direction::SOUTH],
            },
            self::SPLITTER_HORIZONTAL => match ($inputDirection) {
                Direction::NORTH, Direction::SOUTH => [Direction::EAST, Direction::WEST],
                Direction::EAST, Direction::WEST => [$inputDirection],
            },
        };
    }
}
