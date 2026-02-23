<?php

declare(strict_types=1);

namespace Bgl\Core\Security;

final readonly class TokenTtlConfig
{
    public function __construct(
        public int $accessTtl,
        public int $refreshTtl,
    ) {
    }
}
