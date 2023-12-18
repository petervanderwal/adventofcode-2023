<?php

declare(strict_types=1);

namespace App\Tests\Utility;

use App\Model\Point;
use App\Utility\MathUtility;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \App\Utility\MathUtility
 */
class MathUtilityTest extends TestCase
{
    /**
     * @covers ::shoelaceFormula
     */
    public function testShoelaceFormula(): void
    {
        // Example from https://www.101computing.net/the-shoelace-algorithm/
        $this->assertSame(
            32.0,
            MathUtility::shoelaceFormula(
                new Point(2, 7),
                new Point(10, 1),
                new Point(8, 6),
                new Point(11, 7),
                new Point(7, 10),
            )
        );
    }
}