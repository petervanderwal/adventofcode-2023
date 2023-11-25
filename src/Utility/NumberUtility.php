<?php

declare(strict_types=1);

namespace App\Utility;

class NumberUtility
{
    public static function getSign(int|float $number): int
    {
        if ($number === 0) {
            return 0;
        }
        return $number > 0 ? 1 : -1;
    }
}