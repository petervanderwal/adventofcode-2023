<?php

declare(strict_types=1);

namespace App\Model\Matrix;

class RowIterator extends AbstractRowColumnMatrixIterator
{
    public function count(): int
    {
        return $this->matrix->getNumberOfRows();
    }

    protected function getItem(int $index): Row
    {
        return new Row($this->matrix, $index);
    }
}