<?php

declare(strict_types=1);

namespace App\Model\Day17;

use App\Model\Direction;

enum Axis: string
{
    case HORIZONTAL = 'H';
    case VERTICAL = 'V';

    public static function fromDirection(Direction $direction): self
    {
        return match ($direction) {
            Direction::NORTH, Direction::SOUTH => self::VERTICAL,
            Direction::WEST, Direction::EAST => self::HORIZONTAL,
        };
    }

    public function other(): self
    {
        return match ($this) {
            self::HORIZONTAL => self::VERTICAL,
            self::VERTICAL => self::HORIZONTAL,
        };
    }
}
