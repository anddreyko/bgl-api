<?php

declare(strict_types=1);

namespace Tests\Unit\Auth\Enums;

use App\Auth\Enums\UserStatusEnum;
use Codeception\Test\Unit;

/**
 * @covers \App\Auth\Enums\UserStatusEnum
 */
class UserStatusEnumTest extends Unit
{
    public function testIsWait(): void
    {
        $status = UserStatusEnum::wait();
        $this->assertTrue($status->isWait());
    }

    public function testIsActive(): void
    {
        $status = UserStatusEnum::active();
        $this->assertTrue($status->isActive());
    }
}
