<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Auth\PasskeySignInVerify;

final readonly class Result
{
    public function __construct(
        public string $accessToken,
        public string $refreshToken,
        public int $expiresIn,
    ) {
    }
}
