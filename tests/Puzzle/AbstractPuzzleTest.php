<?php

declare(strict_types=1);

namespace App\Tests\Puzzle;

use App\Puzzle\AbstractPuzzle;
use App\Service\Common\AdventOfCodeHttpService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

abstract class AbstractPuzzleTest extends KernelTestCase
{
    protected string $demo1InputFile = 'demo';
    protected int|string|array $expectedDemo1Value;
    protected bool $executeFullAssignment1 = true;
    protected bool $executeParallelAssignment1 = true;
    protected int|string|null $expectedAnswer1Value = null;

    protected string $demo2InputFile = 'demo';
    protected int|string|array $expectedDemo2Value;
    protected bool $executeFullAssignment2 = true;
    protected bool $executeParallelAssignment2 = true;
    protected int|string|null $expectedAnswer2Value = null;

    protected function writeAnswer(int $level, int|string $answer): void
    {
        // Write to terminal
        fwrite(
            STDOUT,
            sprintf(
                "The answer for assignment %d of day %s is: \n%s\n",
                $level,
                $this->getDay(),
                $answer,
            )
        );

        // Submit to Advent of Code
        $adventOfCodeResponse = $this->getAdventOfCodeHttpService()->submitAnswer($this->getDay(), $level, $answer);
        fwrite(
            STDOUT,
            sprintf(
                "I submitted the answer to Advent of Code for you, it replied with: \n%s\n\n",
                $adventOfCodeResponse
            )
        );

        // If Advent of Code replied it was correct, then auto update the unit test
        if (preg_match('/the\s+right\s+answer/i', $adventOfCodeResponse)) {
            $filename = (new \ReflectionClass($this))->getFileName();
            $lineToAdd = sprintf(
                "\n    protected int|string|null \$expectedAnswer%dValue = %s;",
                $level,
                var_export($answer, true)
            );
            $classDefinition = preg_replace_callback(
                '/protected\s+int\\|string\\|array\s+\\$expectedDemo' . $level . 'Value\s+=.*?;/s',
                fn (array $match) => $match[0] . $lineToAdd,
                file_get_contents($filename)
            );
            file_put_contents($filename, $classDefinition);
        }

        exit();
    }

    public function testAssignment1(): void
    {
        $this->testAssignmentNr(
            1,
            $this->executeFullAssignment1,
            $this->executeParallelAssignment1,
            $this->expectedDemo1Value,
            $this->expectedAnswer1Value
        );
    }

    public function testAssignment2(): void
    {
        if (!isset($this->expectedDemo2Value)) {
            $this->markTestIncomplete('No expected demo value configured for assignment 2');
        }
        $this->testAssignmentNr(
            2,
            $this->executeFullAssignment2,
            $this->executeParallelAssignment2,
            $this->expectedDemo2Value,
            $this->expectedAnswer2Value
        );
    }

    private function testAssignmentNr(
        int $nr,
        bool $executeFull,
        bool $executeParallel,
        int|string|array $expectedDemoValue,
        int|string|null $expectedAnswerValue
    ): void {
        $puzzle = $this->getPuzzle();
        $results = [];

        if (!is_array($expectedDemoValue)) {
            $expectedDemoValue = ['' => $expectedDemoValue];
        }

        if ($executeParallel) {
            $puzzles = [];
            foreach ($expectedDemoValue as $key => $value) {
                $puzzles['demo' . $key] = $puzzle->getDemoInput('demo' . $key);
            }
            if ($executeFull) {
                $puzzles['full'] = $puzzle->getFullInput();
            }

            $results = $puzzle->calculateParallelAssignment($nr, $puzzles);

            foreach ($expectedDemoValue as $key => $value) {
                $this->assertSame($value, $results['demo' . $key], sprintf('On demo%s puzzle:', $key));
            }
        } else {
            foreach ($expectedDemoValue as $key => $value) {
                $this->assertSame(
                    $value,
                    $puzzle->{'calculateAssignment' . $nr}($puzzle->getDemoInput('demo' . $key)),
                    sprintf('On demo%s puzzle:', $key)
                );
            }
        }

        if (!$executeFull) {
            $this->markTestIncomplete('Demo assignment ' . $nr . ' passed, but full assignment ' . $nr . ' is disabled at the moment');
        }

        if (!$executeParallel) {
            $results['full'] = $puzzle->{'calculateAssignment' . $nr}($puzzle->getFullInput());
        }

        if ($expectedAnswerValue === null) {
            $this->writeAnswer($nr, $results['full']);
        } else {
            $this->assertSame($expectedAnswerValue, $results['full'], 'On full puzzle:');
        }
    }

    protected function executeTest(callable $tester): void
    {
        $tester($this->getPuzzle());
    }

    protected function getPuzzle(): AbstractPuzzle
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getService($this->getPuzzleClass());
    }

    private function getAdventOfCodeHttpService(): AdventOfCodeHttpService
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getService(AdventOfCodeHttpService::class);
    }

    protected function getService(string $serviceId): object
    {
        return static::getContainer()->get($serviceId);
    }

    protected function getDay(): string
    {
        $className = get_class($this);
        $pattern = sprintf('/^%s\\\\Day(\d{2})Test$/', preg_quote(__NAMESPACE__, '/'));
        if (preg_match($pattern, $className, $matches)) {
            return $matches[1];
        }
        throw new \UnexpectedValueException(sprintf('Puzzle test "%s" isn\'t in expected namespace', $className), 221207213531);

    }

    protected function getPuzzleClass(): string
    {
        return sprintf('App\\Puzzle\\Day%s', $this->getDay());
    }
}