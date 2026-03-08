<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Mates\GetMate;

final readonly class Result
{
    public function __construct(
        public string $id,
        public string $name,
        public ?string $notes,
        public bool $isSystem,
        public string $createdAt,
    ) {
    }
}
