<?php

declare(strict_types=1);

namespace App\Model\Day22;

use App\Model\Iterator\AbstractIterator;
use App\Model\Matrix;
use App\Model\Point;
use Traversable;

/**
 * @extends AbstractIterator<int, Block>
 */
class Tower extends AbstractIterator
{
    public const GROUND_LEVEL = 1;

    /** @var array<int, Block[]> */
    private array $blocksByTopZPosition = [
        self::GROUND_LEVEL => [],
    ];

    public function drop(Block $blockToDrop): void
    {
        $overlappingBlocks = [];
        foreach ($this->blocksByTopZPosition as $blocksInTower) {
            $overlappingBlocks = array_filter($blocksInTower, fn (Block $blockInTower) => $blockInTower->overlapsXYWith($blockToDrop));
            if (!empty($overlappingBlocks)) {
                break;
            }
        }

        $zPosition = $blockToDrop->restOnBlocks(...$overlappingBlocks)->getHigherPoint()->z;
        $this->ensureZPositionIndex($zPosition)->blocksByTopZPosition[$zPosition][] = $blockToDrop;
    }

    private function ensureZPositionIndex(int $zPosition): static
    {
        if (isset($this->blocksByTopZPosition[$zPosition])) {
            return $this;
        }

        for ($key = max(array_keys($this->blocksByTopZPosition)) + 1; $key <= $zPosition; $key++) {
            $this->blocksByTopZPosition[$key] = [];
        }
        krsort($this->blocksByTopZPosition);
        return $this;
    }

    public function getIterator(): Traversable
    {
        foreach ($this->blocksByTopZPosition as $blocks) {
            foreach ($blocks as $block) {
                yield $block;
            }
        }
    }

    public function plotX(): string
    {
        return $this->plot(fn (Point $point) => $point->x);
    }

    public function plotY(): string
    {
        return $this->plot(fn (Point $point) => $point->y);
    }

    /**
     * @param callable(Point): int $onXAxis
     * @return string
     */
    private function plot(callable $onXAxis): string
    {
        $maxX = $this->map(fn (Block $block): int => $onXAxis($block->getHigherPoint()))->max();
        $maxZ = max(array_keys($this->blocksByTopZPosition));

        $matrix = Matrix::fill($maxZ, $maxX + 1, fn () => '.');
        foreach ($this as $block) {
            foreach (range($block->getLowerPoint()->z, $block->getHigherPoint()->z) as $z) {
                foreach (range($onXAxis($block->getLowerPoint()), $onXAxis($block->getHigherPoint())) as $x) {
                    $point = new Point($x, $maxZ - $z);
                    $matrix->setPoint($point, $matrix->getPoint($point) === '.' ? $block->letter : '?');
                }
            }
        }
        return $matrix->plot();
    }
}