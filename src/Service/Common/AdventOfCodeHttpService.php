<?php

declare(strict_types=1);

namespace App\Service\Common;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AdventOfCodeHttpService
{
    public function __construct(
        private HttpClientInterface $adventOfCodeClient
    ) {}

    public function getPuzzleInput(string $day): string
    {
        return $this->adventOfCodeClient->request(
            Request::METHOD_GET,
            sprintf('day/%d/input', $day)
        )->getContent();
    }

    public function submitAnswer(string $day, int $level, int|string $answer): string
    {
        $response = $this->adventOfCodeClient->request(
            Request::METHOD_POST,
            sprintf('day/%d/answer', $day),
            [
                'body' => [
                    'level' => $level,
                    'answer' => $answer,
                ],
            ]
        )->getContent();

        return $this->getArticlePlainText($response);
    }

    private function getArticlePlainText(string $content): string
    {
        if (preg_match('#<article[^>]*>(.*)</article[^>]*>#si', $content, $matches)) {
            $content = $matches[1];
        }
        $content = preg_replace('#</?p[^>]*>#i', "\n", $content);
        return trim(strip_tags($content));
    }
}
