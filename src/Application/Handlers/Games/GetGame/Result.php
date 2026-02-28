<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Games\GetGame;

final readonly class Result
{
    public function __construct(
        public string $id,
        public int $bggId,
        public string $name,
        public ?int $yearPublished,
    ) {
    }
}
