<?php

declare(strict_types=1);

use Bgl\Core\ValueObjects\DateTime;
use Bgl\Core\ValueObjects\Uuid;
use Bgl\Domain\Games\Game;

return [
    Game::class => static fn(array $data): Game => Game::create(
        id: $data['id'] instanceof Uuid ? $data['id'] : new Uuid((string)$data['id'] ?: 'missing'),
        bggId: (int)$data['bggId'],
        name: (string)$data['name'],
        yearPublished: isset($data['yearPublished']) && (int)$data['yearPublished'] !== 0
            ? (int)$data['yearPublished']
            : null,
        createdAt: $data['createdAt'] instanceof DateTime
            ? $data['createdAt']
            : new DateTime((string)$data['createdAt']),
    ),
];
