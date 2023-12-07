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

    public function getRank(Card ...$order): int
    {
        return array_search($this, $order);
    }
}
