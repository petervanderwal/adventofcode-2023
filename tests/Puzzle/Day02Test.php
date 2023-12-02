<?php

declare(strict_types=1);

namespace App\Tests\Puzzle;

/**
 * @coversDefaultClass \App\Puzzle\Day02
 */
class Day02Test extends AbstractPuzzleTest
{
    protected int|string|array $expectedDemo1Value = 8;
    protected int|string|null $expectedAnswer1Value = 2256;

    protected int|string|array $expectedDemo2Value = 2286;
    protected int|string|null $expectedAnswer2Value = 74229;
    protected bool $executeParallelAssignment2 = false;
}
