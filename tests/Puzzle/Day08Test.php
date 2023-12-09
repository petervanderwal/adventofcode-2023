<?php

declare(strict_types=1);

namespace App\Tests\Puzzle;

/**
 * @coversDefaultClass \App\Puzzle\Day08
 */
class Day08Test extends AbstractPuzzleTest
{
    protected int|string|array $expectedDemo1Value = ['1a' => 2, '1b' => 6];
    protected int|string|null $expectedAnswer1Value = 17287;

    protected int|string|array $expectedDemo2Value = ['2a' => 6, '2b' => 24];
    protected int|string|null $expectedAnswer2Value = 18625484023687;
}
