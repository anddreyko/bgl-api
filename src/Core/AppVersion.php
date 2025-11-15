<?php

declare(strict_types=1);

namespace Bgl\Core;

/**
 * @see \Bgl\Tests\Unit\AppVersionCest
 */
final readonly class AppVersion
{
    public function __construct(
        private string $version
    ) {
    }

    public function getVersion(): string
    {
        return $this->version;
    }
}
