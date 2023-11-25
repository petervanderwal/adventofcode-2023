<?php

declare(strict_types=1);

namespace App\Model\Matrix;

/**
 * @method Column[]|iterable getIterator()
 */
class ColumnIterator extends AbstractRowColumnMatrixIterator
{
    public function count(): int
    {
        return $this->matrix->getNumberOfColumns();
    }

    protected function getItem(int $index): Column
    {
        return new Column($this->matrix, $index);
    }
}