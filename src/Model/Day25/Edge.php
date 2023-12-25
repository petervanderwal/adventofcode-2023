<?php

namespace App\Model\Day25;

use App\Algorithm\ShortestPath\Edge as BaseEdge;
use App\Algorithm\ShortestPath\VertexInterface;

class Edge extends BaseEdge
{
    public const ID_SEPARATOR = ',';

    private string $id;

    /**
     * @return array{0: string, 1: string}
     */
    public static function getVertexIdsFromEdgeId(string $id): array
    {
        return explode(static::ID_SEPARATOR, $id);
    }

    public static function generateId(VertexInterface $from, VertexInterface $to): string
    {
        return $from->getVertexIdentifier() < $to->getVertexIdentifier()
            ? $from->getVertexIdentifier() . static::ID_SEPARATOR . $to->getVertexIdentifier()
            : $to->getVertexIdentifier() . static::ID_SEPARATOR . $from->getVertexIdentifier();
    }

    public function getId(): string
    {
        return $this->id ??= static::generateId($this->getFomVertex(), $this->getToVertex());
    }
}