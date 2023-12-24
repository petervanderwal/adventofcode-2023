<?php

declare(strict_types=1);

namespace App\Model\Day24;

use App\Model\Math\LinearFormula;
use Symfony\Component\String\UnicodeString;

class Hailstone
{
    public function __construct(
        public readonly LinearFormula $timeXFormula,
        public readonly LinearFormula $timeYFormula,
        public readonly LinearFormula $timeZFormula,
    ) {}

    private LinearFormula $xyFormula;

    public static function fromString(string|UnicodeString $string): self
    {
        [$positions, $velocities] = explode(' @ ', (string)$string);
        $positions = explode(', ', $positions);
        $velocities = explode(', ', $velocities);
        return new self(
            new LinearFormula((int)$positions[0], (int)$velocities[0]),
            new LinearFormula((int)$positions[1], (int)$velocities[1]),
            new LinearFormula((int)$positions[2], (int)$velocities[2]),
        );
    }

    /**
     * Returns a formula to calculate the y position based on the x position:
     *     $y = $xyFormula($x)
     */
    public function getXyFormula(): LinearFormula
    {
        return $this->xyFormula ??= LinearFormula::getXyFormulaFromTwoTimeFormulas($this->timeXFormula, $this->timeYFormula);
    }
}
