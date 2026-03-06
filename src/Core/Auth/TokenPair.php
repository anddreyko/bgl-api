<?php

declare(strict_types=1);

namespace Bgl\Core\Auth;

final readonly class TokenPair
{
    public function __construct(
        public string $accessToken,
        public string $refreshToken,
    ) {
    }
}
