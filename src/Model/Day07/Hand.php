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

    public function __construct(string $cards)
    {
        foreach (str_split($cards) as $card) {
            $this->cards[] = Card::from($card);
        }
    }

    /**
     * @return Card[]
     */
    public function getCards(): array
    {
        return $this->cards;
    }

    public function getAmountOf(Card $card): int
    {
        return count(array_filter($this->cards, fn (Card $cardInHand) => $cardInHand === $card));
    }

    public function compareCards(Hand $otherHand, Card ...$cardOrder): int
    {
        foreach ($this->cards as $index => $card) {
            if (0 !== $cardCompare = $card->getRank(...$cardOrder) <=> $otherHand->cards[$index]->getRank(...$cardOrder)) {
                return $cardCompare;
            }
        }

        return 0;
    }
}