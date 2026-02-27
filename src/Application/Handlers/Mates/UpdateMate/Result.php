<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Mates\UpdateMate;

final readonly class Result
{
    public function __construct(
        public string $id,
        public string $name,
        public ?string $notes,
    ) {
    }
}
