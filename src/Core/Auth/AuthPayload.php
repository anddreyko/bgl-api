<?php

declare(strict_types=1);

namespace Bgl\Core\Auth;

final readonly class AuthPayload
{
    public function __construct(
        public string $userId,
    ) {
    }
}
