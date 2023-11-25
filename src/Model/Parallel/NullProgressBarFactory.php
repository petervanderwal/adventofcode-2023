<?php

declare(strict_types=1);

namespace App\Model\Parallel;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Webmozarts\Console\Parallelization\Logger\ProgressBarFactory;

class NullProgressBarFactory implements ProgressBarFactory
{
    public function create(OutputInterface $output, int $numberOfItems): ProgressBar
    {
        $progressBar = new ProgressBar(new NullOutput(), $numberOfItems);
        $progressBar->start();
        return $progressBar;
    }
}