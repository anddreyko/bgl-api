<?php

declare(strict_types=1);

namespace Bgl\Core\Security;

final readonly class TokenConfig
{
    private const int MIN_TTL = 60;
    private const int MAX_TTL = 2_592_000;

    public function __construct(
        public int $accessTtl,
        public int $refreshTtl,
    ) {
        if ($accessTtl < self::MIN_TTL || $accessTtl > self::MAX_TTL) {
            throw new \InvalidArgumentException(
                "Access TTL must be between " . self::MIN_TTL . " and " . self::MAX_TTL . " seconds, got {$accessTtl}"
            );
        }

        if ($refreshTtl < self::MIN_TTL || $refreshTtl > self::MAX_TTL) {
            throw new \InvalidArgumentException(
                "Refresh TTL must be between " . self::MIN_TTL . " and " . self::MAX_TTL . " seconds, got {$refreshTtl}"
            );
        }

        if ($refreshTtl <= $accessTtl) {
            throw new \InvalidArgumentException(
                "Refresh TTL ({$refreshTtl}) must be greater than access TTL ({$accessTtl})"
            );
        }
    }
}
