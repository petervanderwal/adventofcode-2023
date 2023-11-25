<?php

declare(strict_types=1);

namespace App\Model\Matrix;

use App\Model\Point;

class Column extends AbstractMatrixRowColumn
{
    public function count(): int
    {
        return $this->matrix->getNumberOfRows();
    }

    protected function getCoordinate(int $index): Point
    {
        return new Point($this->index, $index);
    }
}