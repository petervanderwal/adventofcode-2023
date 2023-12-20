<?php

declare(strict_types=1);

namespace App\Tests\Puzzle;

use App\Puzzle\Day20;

/**
 * @coversDefaultClass \App\Puzzle\Day20
 */
class Day20Test extends AbstractPuzzleTest
{
    protected int|string|array $expectedDemo1Value = [1 => 32000000, 2 => 11687500];
    protected int|string|null $expectedAnswer1Value = 832957356;

    public function testDemo2With3Pushes(): void
    {
        /** @var Day20 $puzzle */
        $puzzle = $this->getPuzzle();
        $broadcaster = $puzzle->parseInput($puzzle->getDemoInput('demo2'));

        $puzzle->pushButton($broadcaster, verbose: true);
        $puzzle->pushButton($broadcaster, verbose: true);
        $puzzle->pushButton($broadcaster, verbose: true);

        $this->expectOutputString(<<<'EOF'

            button -low-> broadcaster
            broadcaster -low-> a
            a -high-> inv
            a -high-> con
            inv -low-> b
            con -high-> output
            b -high-> con
            con -low-> output
            
            button -low-> broadcaster
            broadcaster -low-> a
            a -low-> inv
            a -low-> con
            inv -high-> b
            con -high-> output
            
            button -low-> broadcaster
            broadcaster -low-> a
            a -high-> inv
            a -high-> con
            inv -low-> b
            con -low-> output
            b -low-> con
            con -high-> output
            
            EOF);
    }
}
