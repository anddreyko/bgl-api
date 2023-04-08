<?php

declare(strict_types=1);

namespace App\Auth\Enums;

/**
 * @see \Tests\Unit\Auth\Enums\UserStatusEnumTest
 */
enum UserStatusEnum
{
    case Active;
    case Wait;

    public static function active(): self
    {
        return self::Active;
    }

    public static function wait(): self
    {
        return self::Wait;
    }

    public function isActive(): bool
    {
        return $this == self::Active;
    }

    public function isWait(): bool
    {
        return $this == self::Wait;
    }
}
