<?php

declare(strict_types=1);

namespace App\Model\Operation;

class PowerIntOperation implements IntIntOperationInterface
{
    public function __construct(
        private int $exponent,
    ) {}

    public function __invoke(int $input): int
    {
        return pow($input, $this->exponent);
    }
}