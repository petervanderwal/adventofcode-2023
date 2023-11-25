<?php

declare(strict_types=1);

namespace App\Tests\Utility;

use App\Utility\ArrayUtility;
use PHPUnit\Framework\TestCase;

class ArrayUtilityTest extends TestCase
{
    /**
     * @dataProvider dataProviderGetSlidingChunks
     */
    public function testGetSlidingChunks(array $input, int $chunkSize, array $expected): void
    {
        $this->assertSame($expected, ArrayUtility::getSlidingChunks($input, $chunkSize));
    }

    public function dataProviderGetSlidingChunks(): array
    {
        return [
            'abcdefg size 2' => [
                ['a', 'b', 'c', 'd', 'e', 'f', 'g'],
                2,
                [['a', 'b'], ['b', 'c'], ['c', 'd'], ['d', 'e'], ['e', 'f'], ['f', 'g']],
            ],
            'abcdefg size 3' => [
                ['a', 'b', 'c', 'd', 'e', 'f', 'g'],
                3,
                [['a', 'b', 'c'], ['b', 'c', 'd'], ['c', 'd', 'e'], ['d', 'e', 'f'], ['e', 'f', 'g']],
            ],
            'abc size 3' => [
                ['a', 'b', 'c'],
                3,
                [['a', 'b', 'c']],
            ],
            'abc size 4' => [
                ['a', 'b', 'c'],
                4,
                [['a', 'b', 'c']],
            ],
            'abc size 1' => [
                ['a', 'b', 'c'],
                1,
                [['a'], ['b'], ['c']],
            ],
        ];
    }

    /**
     * @dataProvider dataProviderGetSlidingChunksTooSmallChunkSize
     */
    public function testGetSlidingChunksTooSmallChunkSize(int $chunkSize): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(230921132317);
        ArrayUtility::getSlidingChunks(['a', 'b', 'c'], $chunkSize);
    }

    public function dataProviderGetSlidingChunksTooSmallChunkSize(): iterable
    {
        for ($size = 0; $size > -10; $size--) {
            yield '$chunkSize ' . $size => [$size];
        }
    }

    /**
     * @dataProvider dataProviderGetMedian
     */
    public function testGetMedian(array $numbers, int|float $expected): void
    {
        $this->assertSame($expected, ArrayUtility::getMedian(...$numbers));
    }

    public function dataProviderGetMedian(): array
    {
        return [
            [[0, 1, 2], 1],
            [[1, 0, 2], 1],
            [[2, 3, 16, 3, 1], 3],
            [[1, 2, 3, 4], (2 + 3) / 2],
        ];
    }
}
