<?php

declare(strict_types=1);

namespace App\Model\Matrix;

use App\Model\Point;

class Row extends AbstractMatrixRowColumn
{
    public function count(): int
    {
        return $this->matrix->getNumberOfColumns();
    }

    protected function getCoordinate(int $index): Point
    {
        return new Point($index, $this->index);
    }
}