<?php

declare(strict_types=1);

namespace App\Command;

use App\Model\Parallel\TaskSet;
use App\Puzzle\AbstractPuzzle;
use App\Service\Common\ContainerParametersHelperService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
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

    protected function fetchItems(InputInterface $input, OutputInterface $output): iterable
    {
        $taskSet = $this->getTaskSet($taskSetFile = $input->getOption(self::OPTION_TASK_SET));
        $puzzle = $this->getPuzzle($input);

        $items = [];
        foreach ($taskSet->getTasks() as $taskIndex => $task) {
            if (!is_callable([$puzzle, $task->getMethodName()])) {
                throw new \InvalidArgumentException(sprintf('Task %s::%s is not callable', get_class($puzzle), $task->getMethodName()), 221221174356);
            }

            $items[] = $taskIndex . '@' . $taskSetFile;
        }
        return $items;
    }

    protected function runSingleCommand(string $item, InputInterface $input, OutputInterface $output): void
    {
        [$taskIndex, $taskSetFile] = explode('@', $item, 2);
        $taskSet = $this->getTaskSet($taskSetFile);
        $task = $taskSet->getTask($taskIndex);

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

    protected function getTaskSet(string $taskSetFile): TaskSet
    {
        return unserialize(file_get_contents($taskSetFile));
    }
}