<?php

declare(strict_types=1);

namespace App\Puzzle;

use App\Command\ParallelPuzzleCommand;
use App\Model\Parallel\Task;
use App\Model\Parallel\TaskSet;
use App\Model\PuzzleInput;
use App\Service\Common\ContainerParametersHelperService;
use App\Service\Common\ProgressService;
use App\Service\Common\PuzzleInputService;
use App\Utility\FileWriterUtility;
use Fidry\CpuCoreCounter\CpuCoreCounter;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\Service\Attribute\Required;

abstract class AbstractPuzzle
{
    protected PuzzleInputService $puzzleInputService;
    protected KernelInterface $kernel;
    protected ContainerParametersHelperService $containerParametersHelperService;
    protected ProgressService $progressService;

    #[Required]
    public function setAbstractRequirements(
        PuzzleInputService $puzzleInputService,
        KernelInterface $kernel,
        ContainerParametersHelperService $containerParametersHelperService,
        ProgressService $progressService,
    ) {
        $this->puzzleInputService = $puzzleInputService;
        $this->kernel = $kernel;
        $this->containerParametersHelperService = $containerParametersHelperService;
        $this->progressService = $progressService;
    }

    abstract protected function doCalculateAssignment1(PuzzleInput $input): int|string;

    abstract protected function doCalculateAssignment2(PuzzleInput $input): int|string;

    public function calculateAssignment1(PuzzleInput $input): int|string
    {
        $this->initPuzzle(1, $input);
        return $this->doCalculateAssignment1($input);
    }

    public function calculateAssignment2(PuzzleInput $input): int|string
    {
        $this->initPuzzle(2, $input);
        return $this->doCalculateAssignment2($input);
    }

    private function initPuzzle(int $nr, PuzzleInput $input): void
    {
        $this->progressService->setCurrentPuzzle('day ' . $this->getDay() . ', part ' . $nr . ', ' . ($input->isDemoInput() ? 'demo' : 'full'));
    }

    /**
     * @param array<string, PuzzleInput> $puzzleInputs
     * @return array Puzzle results with the same keys used as within the $puzzleInputs parameter
     */
    public function calculateParallelAssignment(int $nr, array $puzzleInputs): array
    {
        if (!in_array($nr, [1, 2], true)) {
            throw new \InvalidArgumentException('Assignment nr should be 1 or 2', 231004203532);
        }
        $tasks = [];
        foreach ($puzzleInputs as $key => $puzzleInput) {
            if (!$puzzleInput instanceof PuzzleInput) {
                throw new \InvalidArgumentException('Puzzle input should be ' . PuzzleInput::class, 231004203433);
            }
            $tasks[] = (new Task('calculateAssignment' . $nr, $puzzleInput))->setResultKey($key);
        }
        return $this->runParallel(new TaskSet(...$tasks));
    }

    public function getDemoInput(string $file = 'demo'): PuzzleInput
    {
        return $this->puzzleInputService->getPuzzleInput($this->getDay(), $file, true);
    }

    public function getFullInput(): PuzzleInput
    {
        return $this->puzzleInputService->getPuzzleInput($this->getDay(), 'full', false);
    }

    public function getDay(): string
    {
        $className = get_class($this);
        $pattern = sprintf('/^%s\\\\Day(\d{2})$/', preg_quote(__NAMESPACE__, '/'));
        if (preg_match($pattern, $className, $matches)) {
            return $matches[1];
        }
        throw new \UnexpectedValueException(
            sprintf('Puzzle "%s" isn\'t in expected namespace', $className),
            221207210817
        );
    }

    protected function runParallelMethod(string $methodName, mixed ...$tasks): array
    {
        return $this->runParallel(
            new TaskSet(
                ...array_map(
                    fn (mixed $argument) => new Task($methodName, ...(is_array($argument) ? $argument : [$argument])),
                    $tasks
                )
            )
        );
    }

    protected function runParallel(TaskSet $taskSet): array
    {
        $resultsFile = (new Filesystem())->tempnam(
            FileWriterUtility::ensureDir(
                $this->containerParametersHelperService->getCacheDir() . '/parallel-puzzle-job'
            ),
            '',
            '.results.ser'
        );
        $taskSet->setResultsFile($resultsFile);

        $serializedTaskSetFile = substr($resultsFile, 0, -12) . '.task-set.ser';
        file_put_contents($serializedTaskSetFile, serialize($taskSet));

        $application = new Application($this->kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput(
            [
                'command' => ParallelPuzzleCommand::COMMAND_NAME,
                '--' . ParallelPuzzleCommand::OPTION_PUZZLE_DAY => $this->getDay(),
                '--' . ParallelPuzzleCommand::OPTION_TASK_SET => $serializedTaskSetFile,
            ]
        );

        if (!isset($_ENV['WEBMOZARTS_CONSOLE_PARALLELIZATION_CPU_COUNT'])) {
            // Run 3x the amount of CPU's, that'll push the CPU to the limit :)
            $_ENV['WEBMOZARTS_CONSOLE_PARALLELIZATION_CPU_COUNT'] = (new CpuCoreCounter())->getCount() * 3;
        }

        try {
            $application->run($input, new ConsoleOutput());
            return $taskSet->getResults();
        } finally {
            @unlink($resultsFile);
            @unlink($serializedTaskSetFile);
        }
    }

    protected function iterateSteps(int $start = 0, int $max = PHP_INT_MAX, int $step = 1): \Generator
    {
        for ($i = $start;; $i += $step) {
            yield $i;
        }
    }
}