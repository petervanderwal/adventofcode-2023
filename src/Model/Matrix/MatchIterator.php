<?php

declare(strict_types=1);

namespace App\Model\Matrix;

use App\Model\Iterator\AbstractIterator;
use App\Model\Matrix;
use App\Model\Point;
use Traversable;

class MatchIterator extends AbstractIterator
{
    public function __construct(
        private readonly Matrix $matrix,
        private readonly string $pattern,
    ) {}

    /**
     * @return Traversable<MatrixMatch>
     */
    public function getIterator(): Traversable
    {
        foreach ($this->matrix->getRows() as $rowNr => $row) {
            preg_match_all($this->pattern, (string)$row, $matches, PREG_OFFSET_CAPTURE + PREG_SET_ORDER + PREG_UNMATCHED_AS_NULL);
            foreach ($matches as $match) {
                $groups = [];
                foreach ($match as $group => $groupMatch) {
                    if ($group !== 0) {
                        $groups[$group] = $this->createMatch($rowNr, $groupMatch[0], $groupMatch[1]);
                    }
                }
                yield $this->createMatch($rowNr, $match[0][0], (int)$match[0][1], $groups);
            }
        }
    }

    private function createMatch(int $rowNr, ?string $match, ?int $columnNr, array $groups = []): ?MatrixMatch
    {
        if ($match === null) {
            return null;
        }
        return new MatrixMatch($match, new Point($columnNr, $rowNr), $groups);
    }
}
