<?php

declare(strict_types=1);

namespace App\Puzzle;

use App\Model\Day07\Card;
use App\Model\Day07\Hand;
use App\Model\PuzzleInput;
use App\Utility\ArrayUtility;

class Day07 extends AbstractPuzzle
{
    protected function doCalculateAssignment1(PuzzleInput $input): int|string
    {
        $cardOrder = [
            2 => Card::TWO,
            Card::THREE,
            Card::FOUR,
            Card::FIVE,
            Card::SIX,
            Card::SEVEN,
            Card::EIGHT,
            Card::NINE,
            Card::TEN,
            Card::JACK,
            Card::QUEEN,
            Card::KING,
            Card::ACE
        ];

        return $this->calculate($input, $this->getHandScoreMethod(false), ...$cardOrder);
    }

    protected function doCalculateAssignment2(PuzzleInput $input): int|string
    {
        $cardOrder = [
            1 => Card::JACK,
            Card::TWO,
            Card::THREE,
            Card::FOUR,
            Card::FIVE,
            Card::SIX,
            Card::SEVEN,
            Card::EIGHT,
            Card::NINE,
            Card::TEN,
            Card::QUEEN,
            Card::KING,
            Card::ACE
        ];

        return $this->calculate($input, $this->getHandScoreMethod(true), ...$cardOrder);
    }

    private function getHandScoreMethod(bool $useJokers): callable
    {
        return function (Hand $hand) use ($useJokers) {
            if ($useJokers) {
                $cardsWithoutJokers = array_filter($hand->getCards(), fn(Card $card) => $card !== Card::JACK);
                $amountOfJokers = $hand->getAmountOf(Card::JACK);
            } else {
                $cardsWithoutJokers = $hand->getCards();
                $amountOfJokers = 0;
            }

            $uniqueCards = ArrayUtility::unique($cardsWithoutJokers);
            if (count($uniqueCards) === 1 || $amountOfJokers === 5) {
                // 5 of a kind (or we can make it with the jokers)
                return 6;
            }

            if (count($uniqueCards) === 2) {
                // Either 4 of a kind or flush
                // Let's see if we can make 4 of a kind with our jokers
                $amountTheSame = max($hand->getAmountOf($uniqueCards[0]), $hand->getAmountOf($uniqueCards[1])) + $amountOfJokers;
                return $amountTheSame === 4 ? 5 : 4;
            }

            $amounts = array_map($hand->getAmountOf(...), $uniqueCards);
            if (max($amounts) + $amountOfJokers === 3) {
                // 3 of a kind
                return 3;
            }

            if (max($amounts) + $amountOfJokers === 2) {
                // Either 2 pair or 1 pair
                return count($uniqueCards) === 3 ? 2 : 1;
            }

            // High card
            return 0;
        };
    }

    private function calculate(
        PuzzleInput $input,
        callable $scoreCalculation,
        Card ...$cardOrder,
    ): int {
        /** @var array<int, array{hand: Hand, bid: int, score?: int}> $games */
        $games = $input->mapLines(function (string $line) {
            [$hand, $bid] = explode(' ', $line);
            return ['hand' => new Hand($hand), 'bid' => (int)$bid];
        });

        foreach ($games as &$game) {
            $game['score'] = $scoreCalculation($game['hand']);
        }

        usort($games, fn (array $a, $b) =>
            $a['score'] <=> $b['score']
            ?: $a['hand']->compareCards($b['hand'], ...$cardOrder)
        );

        $result = 0;
        foreach ($games as $rank => ['bid' => $bid]) {
            $result += ($rank + 1) * $bid;
        }
        return $result;
    }
}
