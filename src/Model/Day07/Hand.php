<?php

declare(strict_types=1);

namespace App\Model\Day07;

use App\Utility\ArrayUtility;

class Hand
{
    /**
     * @var Card[]
     */
    private array $cards = [];
    private int $score;

    public function __construct(string $cards)
    {
        foreach (str_split($cards) as $card) {
            $this->cards[] = Card::from($card);
        }

        $uniqueCards = ArrayUtility::unique($this->cards);
        if (count($uniqueCards) === 1) {
            // 5 of a kind
            $this->score = 6;
            return;
        }

        if (count($uniqueCards) === 2) {
            // Either 4 of a kind or flush
            $this->score = in_array($this->getAmountOf($uniqueCards[0]), [1, 4]) ? 5 : 4;
            return;
        }

        $amounts = array_map($this->getAmountOf(...), $uniqueCards);
        if (max($amounts) === 3) {
            // 3 of a kind
            $this->score = 3;
            return;
        }

        if (max($amounts) === 2) {
            // Either 2 pair or 1 pair
            $this->score = count($uniqueCards) === 3 ? 2 : 1;
            return;
        }

        // High card
        $this->score = 0;
    }

    public function getAmountOf(Card $card): int
    {
        return count(array_filter($this->cards, fn (Card $cardInHand) => $cardInHand === $card));
    }

    public function compare(Hand $hand): int
    {
        if (0 !== $scoreCompare = $this->score <=> $hand->score) {
            return $scoreCompare;
        }

        foreach ($this->cards as $index => $card) {
            if (0 !== $cardCompare = $card->compare($hand->cards[$index])) {
                return $cardCompare;
            }
        }

        return 0;
    }
}