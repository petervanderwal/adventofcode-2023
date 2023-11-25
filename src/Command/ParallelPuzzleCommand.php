<?php

declare(strict_types=1);

namespace App\Command;

use App\Model\Parallel\NullProgressBarFactory;
use App\Model\Parallel\ParallelLogger;
use App\Model\Parallel\Task;
use App\Model\Parallel\TaskSet;
use App\Puzzle\AbstractPuzzle;
use App\Service\Common\ContainerParametersHelperService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Terminal;
use Webmozarts\Console\Parallelization\Logger\Logger;
use Webmozarts\Console\Parallelization\Logger\StandardLogger;
use Webmozarts\Console\Parallelization\ParallelCommand;
use Webmozarts\Console\Parallelization\ParallelExecutorFactory;

class ParallelPuzzleCommand extends ParallelCommand
{
    public const COMMAND_NAME = 'advent-of-code:puzzle:parallel';
    public const OPTION_PUZZLE_DAY = 'puzzle-day';
    public const OPTION_TASK_SET = 'task-set';

    public function __construct(
        private ContainerParametersHelperService $containerParametersHelperService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        parent::configure();
        $this
            ->setName(self::COMMAND_NAME)
            ->addOption(self::OPTION_PUZZLE_DAY, mode: InputOption::VALUE_REQUIRED)
            ->addOption(self::OPTION_TASK_SET, mode: InputOption::VALUE_REQUIRED);
    }

    protected function configureParallelExecutableFactory(
        ParallelExecutorFactory $parallelExecutorFactory,
        InputInterface $input,
        OutputInterface $output
    ): ParallelExecutorFactory {
        return parent::configureParallelExecutableFactory(
            $parallelExecutorFactory,
            $input,
            $output
        )
            ->withSegmentSize(1)
            ->withBatchSize(1)
            ->withScriptPath($this->containerParametersHelperService->getApplicationRootDir() . '/bin/console');
    }

    protected function createLogger(InputInterface $input, OutputInterface $output): Logger
    {
        return new ParallelLogger(
            new StandardLogger(
                $input,
                $output,
                (new Terminal())->getWidth(),
                new NullProgressBarFactory(),
            )
        );
    }

    protected function fetchItems(InputInterface $input, OutputInterface $output): iterable
    {
        $taskSet = $this->unserialize($input->getOption(self::OPTION_TASK_SET));
        if (!$taskSet instanceof TaskSet) {
            throw new \InvalidArgumentException('Given task set should be TaskSet', 221221173329);
        }

        $puzzle = $this->getPuzzle($input);

        $items = [];
        foreach ($taskSet->getTasks() as $task) {
            if (!is_callable([$puzzle, $task->getMethodName()])) {
                throw new \InvalidArgumentException(sprintf('Task %s::%s is not callable', get_class($puzzle), $task->getMethodName()), 221221174356);
            }

            $items[] = $this->serialize($task);
        }
        return $items;
    }

    protected function runSingleCommand(string $item, InputInterface $input, OutputInterface $output): void
    {
        $task = $this->unserialize($item);
        if (!$task instanceof Task) {
            throw new \InvalidArgumentException('Given task should be Task', 221221174443);
        }

        $puzzle = $this->getPuzzle($input);
        $taskResult = $puzzle->{$task->getMethodName()}(...$task->getArguments());
        $task->storeResult($taskResult);
    }

    protected function getItemName(?int $count): string
    {
        return $count === 1 ? 'task': 'tasks';
    }

    protected function getPuzzle(InputInterface $input): AbstractPuzzle
    {
        $puzzleDay = $input->getOption(static::OPTION_PUZZLE_DAY);
        if (!preg_match('/^[0-9]{2}$/', $puzzleDay)) {
            throw new \InvalidArgumentException('PuzzleDay should be [0-9]{2}');
        }

        $puzzleClass = sprintf('App\\Puzzle\\Day%s', $puzzleDay);

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getContainer()->get($puzzleClass);
    }

    protected function serialize(mixed $data): string
    {
        return base64_encode(serialize($data));
    }

    protected function unserialize(string $serialized): mixed
    {
        return unserialize(base64_decode($serialized));
    }
}