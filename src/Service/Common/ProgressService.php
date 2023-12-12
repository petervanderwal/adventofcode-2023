<?php

declare(strict_types=1);

namespace App\Service\Common;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\StreamOutput;

class ProgressService
{
    private string $currentPuzzle;

    public function setCurrentPuzzle(string $currentPuzzle): void
    {
        $this->currentPuzzle = $currentPuzzle;
    }

    public function iterateWithProgressBar(iterable $iterable, string $message = ''): \Generator
    {
        $progressBar = new ProgressBar(new StreamOutput(STDOUT));

        $progressBar->setFormat(ProgressBar::FORMAT_DEBUG);
        if (is_array($iterable)) {
            $progressBar->setMaxSteps(count($iterable));
        }

        $fullMessage = "\n\nOn puzzle " . $this->currentPuzzle;
        if ($message !== '') {
            $fullMessage .= ', ' . $message;
        }
        $fullMessage .= ": \n";
        fwrite(STDOUT, $fullMessage);

        $progressBar->start();
        foreach ($iterable as $key => $value) {
            yield $key => $value;
            $progressBar->advance();
        }
        $progressBar->finish();

        fwrite(STDOUT, "\n");
    }
}
