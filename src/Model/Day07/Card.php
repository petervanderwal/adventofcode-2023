<?php

declare(strict_types=1);

namespace App\Model\Day07;

enum Card: string
{
    case ACE = 'A';
    case KING = 'K';
    case QUEEN = 'Q';
    case JACK = 'J';
    case TEN = 'T';
    case NINE = '9';
    case EIGHT = '8';
    case SEVEN = '7';
    case SIX = '6';
    case FIVE = '5';
    case FOUR = '4';
    case THREE = '3';
    case TWO = '2';

    public function getRank(): int
    {
        return array_search($this, [
            2 => self::TWO,
            self::THREE,
            self::FOUR,
            self::FIVE,
            self::SIX,
            self::SEVEN,
            self::EIGHT,
            self::NINE,
            self::TEN,
            self::JACK,
            self::QUEEN,
            self::KING,
            self::ACE
        ]);
    }

    public function compare(Card $card): int
    {
        return $this->getRank() <=> $card->getRank();
    }
}
