<?php

declare(strict_types=1);

namespace App\Service\Common;

use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\Service\Attribute\Required;

class DayService
{
    private PuzzleInputService $puzzleInputService;

    public function __construct(
        private readonly ContainerParametersHelperService $containerParametersHelperService,
        private readonly FileWriteService $fileWriteService,
    ) {}

    #[Required] // Can't be done in the constructor due to circular reference
    public function setPuzzleInputService(PuzzleInputService $puzzleInputService): void
    {
        $this->puzzleInputService = $puzzleInputService;
    }

    public function isValidDayNumber(string|int $day): bool
    {
        if (is_string($day)) {
            if (!preg_match('/^[0-9]+$/', $day)) {
                return false;
            }
            $day = (int)$day;
        }
        return $day >= 1 && $day <= 25;
    }

    public function normalizeDay(string|int $day): string
    {
        if (!$this->isValidDayNumber($day)) {
            throw new \InvalidArgumentException('Not a valid day number: ' . $day);
        }
        return sprintf('%02d', $day);
    }

    public function preparePuzzle(int|string $day, string $expectedValue, string $demoInput, SymfonyStyle $output): void
    {
        $day = $this->normalizeDay($day);

        $expectedValueEscaped =
            preg_match('/^[1-9][0-9]*$/', $expectedValue)
            ? $expectedValue // Normal integer
            : var_export($expectedValue, true); // String value

        $this->puzzleInputService->writePuzzleInput($day, 'demo', $demoInput, $output);

        $puzzleFilename = sprintf(
            '%s/src/Puzzle/Day%s.php',
            $this->containerParametersHelperService->getApplicationRootDir(),
            $day
        );
        $testFilename = sprintf(
            '%s/tests/Puzzle/Day%sTest.php',
            $this->containerParametersHelperService->getApplicationRootDir(),
            $day
        );

        $this->fileWriteService->writeToFile(
            $puzzleFilename,
            <<<PHP
            <?php
            
            declare(strict_types=1);
            
            namespace App\Puzzle;
            
            use App\Model\PuzzleInput;
            
            class Day{$day} extends AbstractPuzzle
            {
                protected function doCalculateAssignment1(PuzzleInput \$input): int|string
                {
                    // TODO: Implement calculateAssignment1() method.
                }
            
                protected function doCalculateAssignment2(PuzzleInput \$input): int|string
                {
                    // TODO: Implement calculateAssignment2() method.
                }
            }
            
            PHP,
            output: $output
        );

        $this->fileWriteService->writeToFile(
            $testFilename,
            <<<PHP
            <?php
            
            declare(strict_types=1);
            
            namespace App\Tests\Puzzle;
            
            /**
             * @coversDefaultClass \App\Puzzle\Day{$day}
             */
            class Day{$day}Test extends AbstractPuzzleTest
            {
                protected int|string|array \$expectedDemo1Value = {$expectedValueEscaped};
            }
            
            PHP,
            output: $output
        );

        $output->success('Written puzzle to file ' . $puzzleFilename);
        $output->success('Written test to file ' . $testFilename);

        if ($output->confirm('Open files in PhpStorm?')) {
            exec('phpstorm64.exe --line 10 --column 0 ' . escapeshellarg($testFilename));
            exec('phpstorm64.exe --line 13 --column 8 ' . escapeshellarg($puzzleFilename));
        }
    }
}
