<?php

declare(strict_types=1);

namespace App\Service\Common;

use App\Model\PuzzleInput;
use Safe\Exceptions\FilesystemException;
use Symfony\Component\Console\Style\SymfonyStyle;

class PuzzleInputService
{
    public function __construct(
        private readonly ContainerParametersHelperService $containerParametersHelperService,
        private readonly AdventOfCodeHttpService $adventOfCodeHttpService,
        private readonly DayService $dayService,
        private readonly FileWriteService $fileWriteService,
    ) {}

    public function getPuzzleInputDir(): string
    {
        return $this->containerParametersHelperService->getApplicationRootDir() . '/puzzle-input';
    }

    public function getPuzzleInput(string $day, string $input, bool $isDemoInput): PuzzleInput
    {
        $filepath = $this->getFilepath($day, $input);
        $contents = is_file($filepath) ? file_get_contents($filepath) : false;
        if ($contents === false) {
            if ($isDemoInput) {
                throw new \InvalidArgumentException(sprintf('File "%s" couldn\'t be read', $filepath), 221207205811);
            }
            $contents = $this->retrievePuzzleInput($day, $filepath);
        }

        return (new PuzzleInput(rtrim($contents), $isDemoInput))
            ->replace("\r\n", "\n"); // Normalize line endings
    }

    /**
     * @throws FilesystemException
     */
    public function writePuzzleInput(string $day, string $input, string $data, ?SymfonyStyle $output = null): void
    {
        $this->fileWriteService->writeToFile(
            $this->getFilepath($day, $input),
            $data,
            output: $output
        );
    }

    private function retrievePuzzleInput(string $day, string $filepath): string
    {
        $contents = $this->adventOfCodeHttpService->getPuzzleInput($day);
        $this->fileWriteService->writeToFile($filepath, $contents);
        return $contents;
    }

    public function getFirstNextPuzzleDay(): string
    {
        $dir = $this->getPuzzleInputDir();
        if (!is_dir($dir)) {
            return $this->dayService->normalizeDay(1);
        }

        $max = 0;
        foreach (new \DirectoryIterator($dir) as $child) {
            if ($child->isDir() && $this->dayService->isValidDayNumber($child->getFilename())) {
                $max = max($max, (int)$child->getFilename());
            }
        }
        return $this->dayService->normalizeDay($max + 1);
    }

    private function getFilepath(string $day, string $input): string
    {
        if (!$this->dayService->isValidDayNumber($day)) {
            throw new \InvalidArgumentException('Day is invalid', 221207205324);
        }
        if (!preg_match('/^[\w-]+(\/[\w-]+)*$/', $input)) {
            throw new \InvalidArgumentException(
                'Input should only contain letters, numbers, dashes, underscores and slashes', 221207205324
            );
        }

        return sprintf('%s/%s/%s.txt', $this->getPuzzleInputDir(), $day, $input);
    }
}
