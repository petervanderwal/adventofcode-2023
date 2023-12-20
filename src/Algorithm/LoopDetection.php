<?php

declare(strict_types=1);

namespace App\Algorithm;

use App\Algorithm\LoopDetection\Step;

class LoopDetection
{
    public const DEFAULT_FIELD = '__default-field__';

    /**
     * @var callable
     */
    private $initiatorCallback;

    /**
     * @var callable
     */
    private $stepCallback;

    /**
     * @var array<int, Step>
     */
    private array $steps;
    private ?int $loopStart = null;
    private ?int $loopEnd = null;

    public function __construct(
        private readonly int $amountOfSteps,
        callable $initiatorCallback,
        callable $stepCallback,
    ) {
        $this->initiatorCallback = $initiatorCallback;
        $this->stepCallback = $stepCallback;
    }

    public function run(bool $rerun = false): static
    {
        /** @noinspection PhpStatementHasEmptyBodyInspection */
        foreach ($this->iterate($rerun) as $step) {}
        return $this;
    }

    public function iterate(bool $rerun = false): \Generator
    {
        if (!$rerun && isset($this->steps)) {
            return;
        }

        $this->loopStart = $this->loopEnd = null;

        $initialStep = (new Step(0));
        ($this->initiatorCallback)($initialStep);

        $this->steps = [$initialStep];
        $stepsByState = [$initialStep->getStateStringRepresentation() => 0];

        for ($stepNr = 1; $stepNr <= $this->amountOfSteps; $stepNr++) {
            $step = new Step($stepNr);
            $previousStep = $this->steps[$stepNr - 1];
            ($this->stepCallback)($step, $previousStep->getState());

            $currentStepState = $step->getStateStringRepresentation();
            if (isset($stepsByState[$currentStepState])) {
                $this->loopStart = $stepsByState[$currentStepState];
                $this->loopEnd = $stepNr;
                return;
            }

            $this->steps[] = $step;
            $stepsByState[$currentStepState] = $stepNr;

            yield $step;
        }
    }

    public function getRepeatingEndScore(): int
    {
        return $this->getRepeatingEndScoreForField(self::DEFAULT_FIELD);
    }

    public function getRepeatingEndScoreForField(string $field): int
    {
        $this->run();
        if ($this->loopStart === null) {
            return $this->steps[$this->amountOfSteps]->getScoreForField($field);
        }

        $loopLength = $this->loopEnd - $this->loopStart;
        $positionWithinLoop = ($this->amountOfSteps - $this->loopStart) % $loopLength;
        return $this->steps[$positionWithinLoop + $this->loopStart]->getScoreForField($field);
    }
}
