<?php

declare(strict_types=1);

namespace App\Utility;

class MathUtility
{
    /**
     * To solve: ax^2 + bx + c = 0
     * Calculate x = (-b +/- sqrt(b^2 - 4*a*c))/(2a)
     * @return array{0: float, 1: float} Where index 0 is always the lower number, and index 1 is always the higher number
     */
    public static function abcFormula(int|float $a, int|float $b, int|float $c): array
    {
        return [
            self::_abcFormula($a, $b, $c,  $a > 0 ? -1 : 1),
            self::_abcFormula($a, $b, $c,  $a > 0 ? 1 : -1)
        ];
    }

    /**
     * To solve: ax^2 + bx + c = 0
     * Calculate x = (-b +/- sqrt(b^2 - 4*a*c))/(2a)
     */
    private static function _abcFormula(int|float $a, int|float $b, int|float $c, int $sign): float
    {
        return (-$b + $sign * sqrt(pow($b, 2) - 4 * $a * $c)) / (2 * $a);
    }
}
