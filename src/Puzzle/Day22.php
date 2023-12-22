<?php

declare(strict_types=1);

namespace App\Puzzle;

use App\Model\Day22\Block;
use App\Model\Day22\Tower;
use App\Model\Point;
use App\Model\PuzzleInput;

class Day22 extends AbstractPuzzle
{
    protected function doCalculateAssignment1(PuzzleInput $input): int
    {
        $tower = $this->getTower($input);
//
//        if ($input->isDemoInput()) {
//            echo "\n x\n" . $tower->plotX() . "\n\n";
//            echo "\n y\n" . $tower->plotY() . "\n\n";
//        }

        return count($tower->where(
            function (Block $block) {
                foreach ($block->getIsCarryingBlocks() as $carriedBlock) {
                    if (count($carriedBlock->getIsRestingOnBlocks()) === 1) {
                        // If we remove this block, then this carried block won't have any support anymore
                        return false;
                    }
                }
                return true;
            }
        ));
    }

    protected function doCalculateAssignment2(PuzzleInput $input): int|string
    {
        return $this->getTower($input)->map(
            function (Block $blockToRemove): int {
                $fallingBlocks = [];

                $blocksToTest = $blockToRemove->getIsCarryingBlocks();
                while (count($blocksToTest)) {
                    $newBlocksToTest = [];

                    foreach ($blocksToTest as $block) {
                        if (
                            in_array($block, $fallingBlocks, true)
                            || !$this->isBlockFalling($block, $blockToRemove, ...$fallingBlocks)
                        ) {
                            continue;
                        }

                        $fallingBlocks[] = $block;
                        foreach ($block->getIsCarryingBlocks() as $carryingBlock) {
                            if (!in_array($carryingBlock, $newBlocksToTest, true)) {
                                $newBlocksToTest[] = $carryingBlock;
                            }
                        }
                    }

                    $blocksToTest = $newBlocksToTest;
                }

                return count($fallingBlocks);
            }
        )->sum();
    }

    private function isBlockFalling(Block $block, Block ...$fallingBlocks): bool
    {
        foreach ($block->getIsRestingOnBlocks() as $blockIsRestingOn) {
            if (!in_array($blockIsRestingOn, $fallingBlocks, true)) {
                return false;
            }
        }
        return true;
    }

    private function getTower(PuzzleInput $input): Tower
    {
        $tower = new Tower();
        foreach ($this->getBlocksSorted($input) as $block) {
            $tower->drop($block);
        }
        return $tower;
    }

    /**
     * @return Block[]
     */
    private function getBlocksSorted(PuzzleInput $input): array
    {
        /** @var Block[] $blocks */
        $blocks = $input->mapLines(function (string $line) {
            [$lower, $higher] = explode('~', $line);
            return new Block(Point::fromString($lower), Point::fromString($higher));
        });

        if ($input->isDemoInput()) {
            foreach ($blocks as $index => $block) {
                $block->letter = chr(ord('A') + $index);
            }
        }

        usort($blocks, fn (Block $a, Block $b) => $a->getLowerPoint()->z <=> $b->getLowerPoint()->z);

        return $blocks;
    }
}
