<?php

declare(strict_types=1);

namespace App\Tests\Puzzle;

use App\Model\Day12\Records;
use App\Puzzle\Day12;
use App\Service\Common\PuzzleInputService;

/**
 * @coversDefaultClass \App\Puzzle\Day12
 */
class Day12Test extends AbstractPuzzleTest
{
    protected int|string|array $expectedDemo1Value = 21;
    protected int|string|null $expectedAnswer1Value = 7236;

    protected int|string|array $expectedDemo2Value = 525152;
    protected int|string|null $expectedAnswer2Value = 11607695322318;

    /**
     * @param int[] $groups
     *
     * @dataProvider dataProviderGetAmountOfValidCombinations
     */
    public function testGetAmountOfValidCombinations(string $record, array $groups, int $expected): void
    {
        /** @var Day12 $puzzle */
        $puzzle = $this->getPuzzle();
        $this->assertSame($expected, $puzzle->getAmountOfValidCombinations($record, ...$groups));
    }

    /**
     * @param int[] $groups
     *
     * @dataProvider dataProviderGetAmountOfValidCombinations
     */
    public function testIsAllRecordsObviousFailing(string $record, array $groups): void
    {
        /** @var Day12 $puzzle */
        $puzzle = $this->getPuzzle();

        $result = $puzzle->isAllRecordsObviousFailing(Records::fromString($record), ...$groups);
        // test cache
        $puzzle->isAllRecordsObviousFailing(Records::fromString($record), ...$groups);

        $this->assertFalse($result);
    }

    public function dataProviderGetAmountOfValidCombinations(): array
    {
        /** @var PuzzleInputService $puzzleInputService */
        $puzzleInputService = $this->getService(PuzzleInputService::class);
        return require(
            $puzzleInputService->getPuzzleInputDir() . '/12/test-data-provider.php'
        );
    }
}
