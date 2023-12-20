<?php

declare(strict_types=1);

namespace App\Puzzle;

use App\Model\Day20\Broadcaster;
use App\Model\Day20\Conjunction;
use App\Model\Day20\FlipFlop;
use App\Model\Day20\ModuleInterface;
use App\Model\Day20\Output;
use App\Model\Day20\Pulse;
use App\Model\PuzzleInput;

class Day20 extends AbstractPuzzle
{
    protected function doCalculateAssignment1(PuzzleInput $input): int
    {
        $broadcaster = $this->parseInput($input);

        $result = [Pulse::LOW->value => 0, Pulse::HIGH->value => 0];
        for ($i = 0; $i < 1000; $i++) {
            $pushResult = $this->pushButton($broadcaster);
            foreach ($pushResult as $key => $value) {
                $result[$key] += $value;
            }
        }
        return $result[Pulse::LOW->value] * $result[Pulse::HIGH->value];
    }

    protected function doCalculateAssignment2(PuzzleInput $input): int|string
    {
        // TODO: Implement calculateAssignment2() method.
    }

    public function parseInput(PuzzleInput $input): Broadcaster
    {
        $modulesByName = [];

        $destinationsByName = [];
        foreach ($input->split("\n") as $line) {
            [$name, $destinations] = $line->split(' -> ');
            $module = match (true) {
                $name->startsWith('%') => new FlipFlop(substr((string)$name, 1)),
                $name->startsWith('&') => new Conjunction(substr((string)$name, 1)),
                $name->equalsTo(Broadcaster::NAME) => new Broadcaster((string)$name),
            };

            $modulesByName[$module->getName()] = $module;
            $destinationsByName[$module->getName()] = $destinations->split(', ');
        }

        foreach ($destinationsByName as $source => $destinations) {
            $sourceModule = $modulesByName[$source];

            foreach ($destinations as $destination) {
                if (!isset($modulesByName[(string)$destination])) {
                    $modulesByName[(string)$destination] = new Output((string)$destination);
                }
                $sourceModule->addDestination($modulesByName[(string)$destination]);
            }
        }

        return $modulesByName[Broadcaster::NAME];
    }

    /**
     * @return array{low: int, high: int}
     */
    public function pushButton(Broadcaster $broadcaster, bool $verbose = false): array
    {
        if ($verbose) {
            echo "\n";
        }

        $pulseCount = [
            Pulse::LOW->value => 0,
            Pulse::HIGH->value => 0,
        ];

        $queue = [
            [null, Pulse::LOW, [$broadcaster]]
        ];
        while (count($queue)) {
            /**
             * @var ModuleInterface|null $source
             * @var Pulse $inPulse
             * @var ModuleInterface[] $destinations
             */
            [$source, $inPulse, $destinations] = array_shift($queue);
            foreach ($destinations as $destination) {
                if ($verbose) {
                    echo sprintf(
                        "%s -%s-> %s\n",
                        $source?->getName() ?? 'button',
                        $inPulse->value,
                        $destination->getName(),
                    );
                }

                $pulseCount[$inPulse->value]++;

                $outPulse = $destination->sendPulse($source, $inPulse);
                if ($outPulse !== null) {
                    $queue[] = [$destination, $outPulse, $destination->getDestinations()];
                }
            }
        }

        return $pulseCount;
    }
}
