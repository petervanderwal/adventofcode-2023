<?php

declare(strict_types=1);

namespace App\Service\Common;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\ConsoleSectionOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;

class ProgressService
{
    private string $currentPuzzle;
    private ConsoleOutput $output;

    /**
     * @var ConsoleSectionOutput[]
     */
    private array $outputSections = [];

    public function setCurrentPuzzle(string $currentPuzzle): void
    {
        $this->currentPuzzle = $currentPuzzle;
        $this->outputSections = [];
    }

    private function getOutput(): ConsoleOutput
    {
        if (!isset($this->output)) {
            $this->output = new ConsoleOutput(OutputInterface::VERBOSITY_DEBUG);
            $messagePrefix = "%message%: \n";
            ProgressBar::setFormatDefinition('advent_of_code_first_level', $messagePrefix . ProgressBar::getFormatDefinition('debug'));
            ProgressBar::setFormatDefinition('advent_of_code_first_level_nomax', $messagePrefix . ProgressBar::getFormatDefinition('debug_nomax'));

            $messagePrefix = '%message%: ';
            ProgressBar::setFormatDefinition('advent_of_code_lower_level', $messagePrefix . ProgressBar::getFormatDefinition('debug'));
            ProgressBar::setFormatDefinition('advent_of_code_lower_level_nomax', $messagePrefix . ProgressBar::getFormatDefinition('debug_nomax'));
        }
        return $this->output;
    }

    private function getOutputSection(): ConsoleSectionOutput
    {
        return $this->outputSections[] = $this->getOutput()->section();
    }

    private function isFirstSection(ConsoleSectionOutput $section): bool
    {
        return $section === ($this->outputSections[0] ?? null);
    }

    private function clearSectionExceptFirst(ConsoleSectionOutput $section): void
    {
        $key = array_search($section, $this->outputSections, true);
        if ($key === 0) {
            return;
        }

        $section->clear();
        if ($key !== false) {
            unset($this->outputSections[$key]);
        }
    }

    public function iterateWithProgressBar(iterable $iterable, string $message = ''): \Generator
    {
        $outputSection = $this->getOutputSection();
        $progressBar = new ProgressBar($outputSection);

        if (is_array($iterable)) {
            $count = count($iterable);
            $progressBar->setMaxSteps($count);
            if ($count < 5000) {
                $progressBar->setRedrawFrequency(1);
            }
        }

        $format = 'advent_of_code_lower_level';
        if ($this->isFirstSection($outputSection)) {
            $message = 'On puzzle ' . $this->currentPuzzle . ($message !== '' ? ', ' . $message : '');
            $format = 'advent_of_code_first_level';
        }
        if ($message !== '') {
            $progressBar->setFormat($format);
            $progressBar->setMessage($message);
        }

        $progressBar->start();
        foreach ($iterable as $key => $value) {
            yield $key => $value;
            $progressBar->advance();
        }
        $progressBar->finish();

        $this->clearSectionExceptFirst($outputSection);
    }

    public function showProgressBar(\Generator $generator, string $message = ''): void
    {
        /** @noinspection PhpStatementHasEmptyBodyInspection */
        foreach ($this->iterateWithProgressBar($generator) as $unused) {}
    }
}
