<?php

declare(strict_types=1);

namespace App\Puzzle;

use App\Model\Day12\Record;
use App\Model\Day12\Records;
use App\Model\PuzzleInput;
use App\Utility\NumberUtility;

class Day12 extends AbstractPuzzle
{
    protected function doCalculateAssignment1(PuzzleInput $input): int|string
    {
        $result = 0;
        foreach ($this->parseInput($input) as [$record, $group]) {
            $result += $this->getAmountOfValidCombinations($record, ...$group);
        }
        return $result;
    }

    protected function doCalculateAssignment2(PuzzleInput $input): int|string
    {
        $tasks = [];
        foreach ($this->parseInput($input) as [$record, $group]) {
            // Repeat record 5 times, separated by '?'
            $record = str_repeat($record . '?', 4) . $record;
            // Repeat group 5 times
            $group = array_merge(...array_fill(0, 5, $group));
            // And do the same calculation
            $tasks[] = [$record, ...$group];
        }
        return array_sum(
            $this->runParallelMethod(
                'getAmountOfValidCombinations',
                ...$tasks
            )
        );
    }

    /**
     * @return array<int, array{0: string, 1: int[]}>
     */
    private function parseInput(PuzzleInput $input): array
    {
        return $input->mapLines(function (string $line) {
            [$record, $groups] = explode(' ', $line);
            return [$record, NumberUtility::getNumbersFromLine($groups)];
        });
    }

    public function getAmountOfValidCombinations(string $record, int ...$groups): int
    {
        return $this->getAmountOfValidCombinationsForAllRecords(Records::fromString($record), ...$groups);
    }

    private function getAmountOfValidCombinationsForAllRecords(Records $records, int ...$groups): int
    {
        if ($this->isAllRecordsObviousFailing($records, ...$groups)) {
            return 0;
        }

        if (count($records->records) === 1) {
            return $this->getAmountOfValidCombinationsForSingleRecord($records->records[0], ...$groups);
        }

        $result = 0;
        $remainingRecords = $records->sliceOffset(1);
        for ($amountOfGroupsInFirstRecord = 0; $amountOfGroupsInFirstRecord <= count($groups); $amountOfGroupsInFirstRecord++) {
            $combinationsFirstRecord = $this->getAmountOfValidCombinationsForSingleRecord(
                $records->records[0],
                ...array_slice($groups, 0, $amountOfGroupsInFirstRecord)
            );
            if ($combinationsFirstRecord === 0) {
                continue;
            }

            $combinationsRemainingRecords = $this->getAmountOfValidCombinationsForAllRecords(
                $remainingRecords,
                ...array_slice($groups, $amountOfGroupsInFirstRecord)
            );
            $result += $combinationsFirstRecord * $combinationsRemainingRecords;
        }
        return $result;
    }

    private function getAmountOfValidCombinationsForSingleRecord(Record $record, int ...$groups): int
    {
        static $cache = [];

        if ($this->isSingleRecordObviousFailing($record, ...$groups)) {
            return 0;
        }

        $cacheKey = implode(',', $groups) . ' in ' . $record->cacheKey;
        if (isset($cache[$cacheKey])) {
            return $cache[$cacheKey];
        }

        if (count($record->parts) === 1) {
            // Only ?'s
            return $cache[$cacheKey] = $this->getAmountOfOptionsForOnlyQuestionMarks($record->parts[0], ...$groups);
        }

        $result = 0;
        $remainingRecord = $record->sliceOffFirstGroup();
        for ($amountOfGroupsInFirstPart = 0; $amountOfGroupsInFirstPart <= count($groups) - 1; $amountOfGroupsInFirstPart++) {
            $groupInSecondPart = $groups[$amountOfGroupsInFirstPart];
            if ($groupInSecondPart < $record->parts[1]) {
                continue; // This group is smaller than the amount of #'s
            }

            if ($amountOfGroupsInFirstPart === 0) {
                $groupsInFirstPart = [];
            } else {
                $groupsInFirstPart = array_slice($groups, 0, $amountOfGroupsInFirstPart);
                $minimumLengthForGroupsInFirstPart = $this->getMinimumLengthRequired(...$groupsInFirstPart);
                if ($minimumLengthForGroupsInFirstPart > $record->parts[0] - 1) {
                    break; // Maximum length hit
                }
            }

            $remainingGroups = array_slice($groups, $amountOfGroupsInFirstPart + 1);
            $minimumLengthForRemainingGroups = $this->getMinimumLengthRequired(...$remainingGroups);

            $minGroupStartBefore = max(
                0,
                $groupInSecondPart - $record->parts[1] - $remainingRecord->totalLength
            );
            $maxGroupStartBefore = min(
                $groupInSecondPart - $record->parts[1], // If our group is 5 large, and we already have 2, then max 3 before
                empty($groupsInFirstPart)
                        ? $record->parts[0] // Eat up all the ?'s before
                        : $record->parts[0] - $minimumLengthForGroupsInFirstPart - 1 // Or eat up all, minus the length for the first groups minus one extra dot after that first groups
            );
            foreach (range($minGroupStartBefore, $maxGroupStartBefore) as $groupStartBefore) {
                $groupLengthAfter = $groupInSecondPart - $record->parts[1] - $groupStartBefore;

                if (isset($record->parts[3])) {
                    if ($groupLengthAfter < $record->parts[2]) {
                        $amountOfOptionsForRemainingGroups = $this->getAmountOfValidCombinationsForSingleRecord(
                            $remainingRecord->eatUpFirstQuestionMarks($groupLengthAfter + 1),
                            ...$remainingGroups
                        );
                    } elseif ($groupLengthAfter >= $record->parts[2] + $record->parts[3]) {
                        $amountOfOptionsForRemainingGroups = $this->getAmountOfValidCombinationsForSingleRecord(
                            $remainingRecord->eatUpFirstQuestionMarks($record->parts[2]),
                            $groupLengthAfter - $record->parts[2],
                            ...$remainingGroups
                        );
                    } else {
                        continue; // Option not possible: we're eating up all the ?'s but none or not all of the #'s
                                  // after it
                    }
                } else {
                    if (count($remainingGroups)) {
                        $remainingQuestionMarks = $record->parts[2] - $groupLengthAfter - 1;
                        if ($remainingQuestionMarks < $minimumLengthForRemainingGroups) {
                            continue;
                        }
                        $amountOfOptionsForRemainingGroups = $this->getAmountOfOptionsForOnlyQuestionMarks($remainingQuestionMarks, ...$remainingGroups);
                    } else {
                        $amountOfOptionsForRemainingGroups = 1;
                    }
                }

                if ($amountOfOptionsForRemainingGroups === 0) {
                    continue;
                }

                $result += $this->getAmountOfOptionsForOnlyQuestionMarks(
                    $record->parts[0] - $groupStartBefore - 1,
                    ...$groupsInFirstPart
                ) * $amountOfOptionsForRemainingGroups;
            }
        }

        return $cache[$cacheKey] = $result;
    }

    public function isAllRecordsObviousFailing(Records $records, int ...$groups): bool
    {
        static $cache;

        if (count($records->records) === 1) {
            return $this->isSingleRecordObviousFailing($records->records[0], ...$groups);
        }

        $cacheKey = implode(',', $groups) . ' in ' . $records->cacheKey;
        if (isset($cache[$cacheKey])) {
            return $cache[$cacheKey];
        }

        $remainingRecords = $records->sliceOffset(1);
        for ($amountOfGroupsInFirstRecord = 0; $amountOfGroupsInFirstRecord <= count($groups); $amountOfGroupsInFirstRecord++) {
            if (
                !$this->isSingleRecordObviousFailing($records->records[0], ...array_slice($groups, 0, $amountOfGroupsInFirstRecord))
                && !$this->isAllRecordsObviousFailing($remainingRecords, ...array_slice($groups, $amountOfGroupsInFirstRecord))
            ) {
                return $cache[$cacheKey] = false;
            }
        }

        // All options are failing
        return $cache[$cacheKey] = true;
    }

    private function isSingleRecordObviousFailing(Record $record, int ...$groups): bool
    {
        static $cache = [];

        $cacheKey = implode(',', $groups) . ' in ' . $record->cacheKey;
        if (isset($cache[$cacheKey])) {
            return $cache[$cacheKey];
        }

        if (count($groups) === 0) {
            // No groups, then this is failing if there are 1 or more #'s
            return $cache[$cacheKey] = count($record->parts) > 1;
        }
        if ($this->getMinimumLengthRequired(...$groups) > $record->totalLength) {
            // If the required length for the given groups is longer than the total record length, then this is failing
            return $cache[$cacheKey] = true;
        }
        if (count($record->parts) === 1) {
            // There are no #'s in our pattern, so only ?'s. In above validation we already determined that the length
            // is enough, so this record won't be failing.
            return $cache[$cacheKey] = false;
        }

        // Now somewhat more sophisticated check: are we able to map the #'s in the record to the groups?
        // Note this is still a dirty check, solely to exclude the most obvious failing options
        for ($amountOfGroupsInFirstPart = 0; $amountOfGroupsInFirstPart <= count($groups) - 1; $amountOfGroupsInFirstPart++) {
            $groupsInFirstPart = array_slice($groups, 0, $amountOfGroupsInFirstPart);
            if ($this->getMinimumLengthRequired(...$groupsInFirstPart) > $record->parts[0]) {
                break; // Maximum length hit
            }

            $groupInSecondPart = $groups[$amountOfGroupsInFirstPart];
            if ($groupInSecondPart < $record->parts[1]) {
                continue; // This group is smaller than the amount of #'s
            }

            return $cache[$cacheKey] = false;
        }

        // Nothing found that seems to be possible
        return $cache[$cacheKey] = true;
    }

    private function getMinimumLengthRequired(int ...$groups): int
    {
        return max(0, array_sum($groups) + count($groups) - 1);
    }

    // .726 sec
    public function old(string $record, int ...$groups): int
    {
        // Note: due to trimming on dots, it's guaranteed the $record doesn't start or end with a dot
        if ($record === '') {
            // Record can be empty due to trimming. If we have any groups left, then this is an invalid option,
            // otherwise this is our single option.
            return empty($groups) ? 1 : 0;
        }

        $minGroupsLength = max(0, array_sum($groups) + count($groups) - 1);
        $absoluteMaxAmountOfPrependSpaces = strlen($record) - $minGroupsLength;
        if ($absoluteMaxAmountOfPrependSpaces < 0) {
            // No options possible, the pattern is shorter than the amount of space required to build the groups
            return 0;
        }

        $currentGroup = array_shift($groups);
        $amountOfSpacesOptions = [];
        $amountOfSpacesOptionsWithoutUsingGroup = [];

        $firstHash = strpos($record, '#');
        if ($currentGroup === null) {
            // No group remaining we have only one option and that is to fill everything with spaces.
            // If that's not possible because our record contains a "#" then we have no options at all.
            return $firstHash === false ? 1 : 0;
        }

        $firstDot = strpos($record, '.');
        if ($firstDot !== false) {
            if ($firstHash === false || $firstDot < $firstHash) {
                // Pattern is "?.?#" with any amount >= 1 of the first "?" and the "." and any amount >=0 of the "#" and
                // the second "?". Here $firstDot is equalling the amount of the first "?"
                if ($firstDot >= $currentGroup) {
                    // For a $currentGroup = 2 and a pattern of "????.?#?" we can have "##.?.?#?" or ".##..?#?" or
                    // "..##.?#?" with the "#" on one of the "?"...
                    $groupShouldStartAt = $firstDot - $currentGroup;
                    if ($groupShouldStartAt >= 0) {
                        $amountOfSpacesOptions = range(0, min($groupShouldStartAt, $absoluteMaxAmountOfPrependSpaces));
                    }
                }
                // ... or all leading question marks are equal to dot
                // Note, with "???..??" or "???..#?" we will have 5 leading spaces
                $amountOfFullDots = min(
                    $firstHash ?: PHP_INT_MAX,
                    strpos($record, '?', $firstDot + 1) ?: PHP_INT_MAX
                );
                if ($amountOfFullDots <= $absoluteMaxAmountOfPrependSpaces) {
                    $amountOfSpacesOptionsWithoutUsingGroup[] = $amountOfFullDots;
                }
            } else { // $firstDot > $firstHash
                // Pattern is "?#?." with any amount >=0 of "?" and any amount >= 1 of "#" and "."
                $amountOfSpacesOptions = $this->getSpaceOptionsWhereHashIsFirstCharacter(
                    $currentGroup,
                    $record,
                    $firstHash,
                    $firstDot,
                    $absoluteMaxAmountOfPrependSpaces
                );
            }
        } elseif ($firstHash !== false) {
            // Pattern is "?#?" with any amount >= 0 of "?" and any amount >= 1 of "#". Here $firstHash is equalling
            // the amount of first "?"
            $amountOfSpacesOptions = $this->getSpaceOptionsWhereHashIsFirstCharacter(
                $currentGroup,
                $record,
                $firstHash,
                $firstDot,
                $absoluteMaxAmountOfPrependSpaces
            );
        } else { // $firstDot === false && $firstHash === false
            // Pattern is all "???"
            if ($currentGroup === null) {
                // Only one option, all dots
                return 1;
            }

            // Note we shifted the current group above, now appending it again for our calculation
            $amountOfGroups = count($groups) + 1;
            $amountOfHashSymbols = array_sum($groups) + $currentGroup;
            return $this->_getAmountOfOptionsForOnlyQuestionMarks(
                strlen($record) - $amountOfHashSymbols - ($amountOfGroups - 1),
                $amountOfGroups
            );
        }

        $result = 0;
        foreach ($amountOfSpacesOptions as $amountOfPrependSpaces) {
            $result += $this->old(
                // Dots at the start don't have any effect on the outcome, trim for performance (the ones at the end are already trimmed)
                // Reverse both record and groups so that we're narrowing from outwards (alternating left/right) to the middle
                // Note that we are subtracting
                strrev(ltrim(substr($record, $amountOfPrependSpaces + $currentGroup + 1), '.')),
                ...array_reverse($groups)
            );
        }
        foreach ($amountOfSpacesOptionsWithoutUsingGroup as $amountOfPrependSpaces) {
            $result += $this->old(
                // Dots at the start don't have any effect on the outcome, trim for performance (the ones at the end are already trimmed)
                ltrim(substr($record, $amountOfPrependSpaces), '.'),
                $currentGroup,
                ...$groups
            );
        }
        return $result;
    }

    /**
     * @return int[]
     */
    private function getSpaceOptionsWhereHashIsFirstCharacter(
        int $currentGroup,
        string $record,
        int $firstHash,
        int|false $firstDot,
        int $absoluteMaxAmountOfPrependSpaces,
    ): array {
        // Pattern is "?#?." with any amount >=0 of "?" and "." and any amount >= 1 of "#"

        $amountOfSpacesOptions = [];

        // Test if we can push the current group in all the "?" we have before the first hash
        $maxAmountOfSpacesWithGroupBeforeGivenHash = $firstHash - $currentGroup - 1; // Note the - 1, we need one space behind the group
        if ($maxAmountOfSpacesWithGroupBeforeGivenHash >= 0) {
            $amountOfSpacesOptions = range(0, $maxAmountOfSpacesWithGroupBeforeGivenHash);
        }

        // Try to push the current group at the place of the "#" symbol

        // Determine where the series of "#" symbols ends, this will be the full group
        $hashEnds = min(
            strpos($record, '.', $firstHash + 1) ?: strlen($record),
            strpos($record, '?', $firstHash + 1) ?: strlen($record),
        );
        if ($hashEnds - $firstHash > $currentGroup) {
            // We have more fixed "#" symbols than the current group. This group can't be pushed in that "#" range
            return $amountOfSpacesOptions;
        }

        // Calculate the minimum amount of spaces
        $minAmountOfSpaces = max(0, $hashEnds - $currentGroup);
        if ($minAmountOfSpaces > $absoluteMaxAmountOfPrependSpaces) {
            // It's not possible to use the found "#" symbol for the current group, we need more spaces than possible to
            // reach that one
            return $amountOfSpacesOptions;
        }

        // Calculate the maximum amount of spaces
        $maxAmountOfSpaces = min(
            // The group starts at the given "#"
            $firstHash,
            // If we have a dot, then we need to finish our group before this dot
            $firstDot === false ? PHP_INT_MAX : $firstDot - $currentGroup,
            $absoluteMaxAmountOfPrependSpaces
        );
        if ($maxAmountOfSpaces < 0) {
            // It's not possible to use the found "#" symbol for the current group, the group should start at a position
            // before the current record to make it fit
            return $amountOfSpacesOptions;
        }

        // Filter out all the options where our group will end right before the next '#' symbol
        $range = array_filter(
            range($minAmountOfSpaces, $maxAmountOfSpaces),
            fn (int $amountOfSpaces) =>
                $amountOfSpaces + $currentGroup === strlen($record)
                || $record[$amountOfSpaces + $currentGroup] !== '#'
        );

        return empty($amountOfSpacesOptions)
            ? $range
            : array_unique([
                ...$amountOfSpacesOptions,
                ...$range,
            ]);
    }

    private function getAmountOfOptionsForOnlyQuestionMarks(int $amountOfQuestionMarks, int ...$groups): int
    {
        $amountOfGroups = count($groups);
        return $this->_getAmountOfOptionsForOnlyQuestionMarks(
            $amountOfQuestionMarks - array_sum($groups) - ($amountOfGroups - 1),
            $amountOfGroups
        );
    }

    private function _getAmountOfOptionsForOnlyQuestionMarks(int $amountOfQuestionMarks, int $amountOfGroups): int
    {
        static $cache;

        if ($amountOfGroups === 0) {
            return 1;
        }
        if ($amountOfGroups === 1) {
            return $amountOfQuestionMarks + 1;
        }

        if (isset($cache[$amountOfQuestionMarks][$amountOfGroups])) {
            return $cache[$amountOfQuestionMarks][$amountOfGroups];
        }

        $result = 0;
        for ($i = 0; $i <= $amountOfQuestionMarks; $i++) {
            $result += $this->_getAmountOfOptionsForOnlyQuestionMarks($i, $amountOfGroups - 1);
        }
        return $cache[$amountOfQuestionMarks][$amountOfGroups] = $result;
    }
}
