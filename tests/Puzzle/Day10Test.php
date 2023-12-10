<?php

declare(strict_types=1);

namespace App\Tests\Puzzle;

/**
 * @coversDefaultClass \App\Puzzle\Day10
 */
class Day10Test extends AbstractPuzzleTest
{
    protected int|string|array $expectedDemo1Value = 8;
    protected int|string|null $expectedAnswer1Value = 6682;

    protected int|string|array $expectedDemo2Value = ['2a' => 4, '2b' => 8, '2c' => 10];
    protected int|string|null $expectedAnswer2Value = 353;
}
