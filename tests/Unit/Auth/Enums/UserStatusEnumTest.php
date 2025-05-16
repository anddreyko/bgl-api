<?php

declare(strict_types=1);

namespace Tests\Unit\Auth\Enums;

use App\Domain\Auth\Enums\UserStatusEnum;
use Codeception\Test\Unit;

/**
 * @covers \App\Domain\Auth\Enums\UserStatusEnum
 */
class UserStatusEnumTest extends Unit
{
    public function testIsWait(): void
    {
        $status = UserStatusEnum::Wait;
        $this->assertTrue($status->isWait());
    }

    public function testIsActive(): void
    {
        $status = UserStatusEnum::Active;
        $this->assertTrue($status->isActive());
    }
}
