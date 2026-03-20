<?php

declare(strict_types=1);

namespace Bgl\Core\Auth;

final readonly class Credentials
{
    /**
     * @param non-empty-string $code  Short credential (e.g. 6-digit)
     * @param non-empty-string $token Long credential (e.g. UUID)
     */
    public function __construct(
        public string $code,
        public string $token,
    ) {
    }
}
