<?php

declare(strict_types=1);

namespace App\Model\Matrix;

use Traversable;

abstract class AbstractRowColumnMatrixIterator extends AbstractMatrixIterator
{
    abstract protected function getItem(int $index): AbstractMatrixRowColumn;

    /**
     * @return Traversable|AbstractMatrixRowColumn[]
     */
    public function getIterator(): Traversable
    {
        yield from parent::getIterator();
    }
}