<?php

declare(strict_types=1);

namespace App\Tests\Puzzle;

/**
 * @coversDefaultClass \App\Puzzle\Day01
 */
class Day01Test extends AbstractPuzzleTest
{
    protected int|string|array $expectedDemo1Value = 142;
    protected int|string|null $expectedAnswer1Value = 54573;

    protected int|string|array $expectedDemo2Value = [2 => 281];
    protected int|string|null $expectedAnswer2Value = 54591;
}
