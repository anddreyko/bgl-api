<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\User\UpdateUser;

final readonly class Result
{
    public function __construct(
        public string $id,
        public string $email,
        public string $name,
        public bool $isActive,
        public string $createdAt,
    ) {
    }
}
