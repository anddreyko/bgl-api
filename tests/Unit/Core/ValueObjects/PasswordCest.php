<?php

declare(strict_types=1);

namespace Bgl\Tests\Unit\Core\ValueObjects;

use Bgl\Core\ValueObjects\Password;
use Bgl\Tests\Support\UnitTester;
use Codeception\Attribute\Group;

/**
 * @covers \Bgl\Core\ValueObjects\Password
 *
 * @see \Bgl\Core\ValueObjects\Password
 */
#[Group('core', 'value-object')]
final class PasswordCest
{
    public function testValidPassword(UnitTester $i): void
    {
        $password = new Password('12345678');

        $i->assertSame('12345678', $password->getValue());
    }

    public function testShortPasswordThrows(UnitTester $i): void
    {
        $i->expectThrowable(
            \InvalidArgumentException::class,
            static function (): void {
                new Password('short');
            },
        );
    }

    public function testOneBelowMinLengthThrows(UnitTester $i): void
    {
        $i->expectThrowable(
            \InvalidArgumentException::class,
            static function (): void {
                new Password('1234567');
            },
        );
    }

    public function testExactMinLength(UnitTester $i): void
    {
        $password = new Password('12345678');

        $i->assertSame(Password::MIN_LENGTH, mb_strlen($password->getValue()));
    }

    public function testEmptyPasswordThrows(UnitTester $i): void
    {
        $i->expectThrowable(
            \InvalidArgumentException::class,
            static function (): void {
                new Password('');
            },
        );
    }
}
