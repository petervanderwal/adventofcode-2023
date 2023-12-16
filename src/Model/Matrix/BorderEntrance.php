<?php

declare(strict_types=1);

namespace App\Model\Matrix;

use App\Model\Direction;
use App\Model\Point;

class BorderEntrance
{
    public function __construct(
        public readonly Point $point,
        public readonly Direction $direction,
    ) {}
}
