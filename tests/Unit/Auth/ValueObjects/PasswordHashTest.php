<?php

declare(strict_types=1);

namespace Tests\Unit\Auth\ValueObjects;

use App\Core\ValueObjects\PasswordHash;
use Codeception\Test\Unit;

/**
 * @covers \App\Core\ValueObjects\PasswordHash
 */
class PasswordHashTest extends Unit
{
    public function testSuccessful(): void
    {
        $id = new PasswordHash('password-hash');

        $this->assertEquals('password-hash', $id->getValue());
    }

    public function testEmpty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new PasswordHash('');
    }

    public function testSpace(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new PasswordHash(' ');
    }
}
