<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Mates\CreateMate;

final readonly class Result
{
    public function __construct(
        public string $id,
        public string $name,
        public ?string $notes,
        public string $createdAt,
    ) {
    }
}
