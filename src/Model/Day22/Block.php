<?php

declare(strict_types=1);

namespace App\Model\Day22;

use App\Model\Point;

class Block
{
    public string $letter;

    /**
     * @var Block[]
     */
    private array $isRestingOnBlocks = [];

    /**
     * @var Block[]
     */
    private array $isCarryingBlocks = [];

    public function __construct(
        private Point $lowerPoint,
        private Point $higherPoint,
    ) {}

    public function getLowerPoint(): Point
    {
        return $this->lowerPoint;
    }

    public function getHigherPoint(): Point
    {
        return $this->higherPoint;
    }

    public function overlapsXYWith(Block $block): bool
    {
        return $block->higherPoint->x >= $this->lowerPoint->x
            && $block->lowerPoint->x <= $this->higherPoint->x
            && $block->higherPoint->y >= $this->lowerPoint->y
            && $block->lowerPoint->y <= $this->higherPoint->y;
    }

    public function restOnBlocks(Block ...$blocksToRestOn): static
    {
        if (empty($blocksToRestOn)) {
            return $this->restOnGround();
        }

        $this->isRestingOnBlocks = $blocksToRestOn;
        foreach ($blocksToRestOn as $block) {
            $block->isCarryingBlocks[] = $this;
        }
        return $this->moveDown($this->lowerPoint->z - $blocksToRestOn[0]->higherPoint->z - 1);
    }

    public function restOnGround(): static
    {
        return $this->moveDown($this->lowerPoint->z - Tower::GROUND_LEVEL);
    }

    /**
     * @return Block[]
     */
    public function getIsRestingOnBlocks(): array
    {
        return $this->isRestingOnBlocks;
    }

    /**
     * @return Block[]
     */
    public function getIsCarryingBlocks(): array
    {
        return $this->isCarryingBlocks;
    }

    private function moveDown(int $steps): static
    {
        $this->lowerPoint = $this->lowerPoint->moveZ(-$steps);
        $this->higherPoint = $this->higherPoint->moveZ(-$steps);
        return $this;
    }
}