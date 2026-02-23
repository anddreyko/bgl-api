<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\User\GetUser;

final readonly class Result
{
    public function __construct(
        public string $id,
        public string $email,
        public bool $isActive,
        public string $createdAt,
        public ?string $name = null,
    ) {
    }
}
