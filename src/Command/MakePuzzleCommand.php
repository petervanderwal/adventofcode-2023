<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\Common\DayService;
use App\Service\Common\PuzzleInputService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand('make:puzzle')]
class MakePuzzleCommand extends Command
{
    public const ARGUMENT_DAY = 'day';
    public const ARGUMENT_EXPECTED_VALUE = 'expected-value';
    public const ARGUMENT_DEMO_INPUT = 'demo-input';

    private InputInterface $input;
    private OutputInterface $output;
    private SymfonyStyle $outputStyle;

    public function __construct(
        private DayService $dayService,
        private PuzzleInputService $puzzleInputService,
    ) {
        parent::__construct();
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->input = $input;
        $this->output = $output;
        $this->outputStyle = new SymfonyStyle($input, $output);
    }

    protected function configure(): void
    {
        $this->addArgument(
                static::ARGUMENT_DAY,
                InputArgument::REQUIRED,
                'The day number for which you want to create the puzzle'
            )
            ->addArgument(
                static::ARGUMENT_EXPECTED_VALUE,
                InputArgument::REQUIRED,
                'The expected value for demo 1'
            )
            ->addArgument(
                static::ARGUMENT_DEMO_INPUT,
                InputArgument::REQUIRED,
                'The demo input to use in your test'
            )
        ;
    }

    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        $notEmpty = function (?string $value) {
            if ($value !== null && trim($value) !== '') {
                return $value;
            }
            throw new \InvalidArgumentException('Value can\'t be empty');
        };

        $this->ensureArgument(
            static::ARGUMENT_DAY,
            $this->dayService->normalizeDay(...),
            new Question(
                'Enter day number',
                default: $this->puzzleInputService->getFirstNextPuzzleDay()
            )
        );

        $this->ensureArgument(
            static::ARGUMENT_EXPECTED_VALUE,
            $notEmpty,
            new Question('Enter expected value for the first demo')
        );

        $this->ensureArgument(
            static::ARGUMENT_DEMO_INPUT,
            $notEmpty,
            (new Question('Enter the demo input'))->setMultiline(true)
        );
    }

    protected function ensureArgument(string $argument, callable $validator, Question $question): void
    {
        if (null !== ($value = $this->input->getArgument($argument))) {
            // Normalize input value
            try {
                $value = $validator($value);
                $this->input->setArgument($argument, $value);
                return;
            } catch (\Exception) {
                // Invalid value, continue with question
            }
        }

        $this->input->setArgument(
            $argument,
            $this->outputStyle->askQuestion($question->setValidator($validator))
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->dayService->preparePuzzle(
            $this->input->getArgument(static::ARGUMENT_DAY),
            $this->input->getArgument(static::ARGUMENT_EXPECTED_VALUE),
            $this->input->getArgument(static::ARGUMENT_DEMO_INPUT),
            $this->outputStyle,
        );

        return static::SUCCESS;
    }
}
