<?php

declare(strict_types=1);

namespace App\Domain\Auth\Enums;

/**
 * @see \Tests\Unit\Auth\Enums\UserStatusEnumTest
 */
enum UserStatusEnum
{
    case Active;
    case Wait;

    public function isActive(): bool
    {
        return $this == self::Active;
    }

    public function isWait(): bool
    {
        return $this == self::Wait;
    }
}
