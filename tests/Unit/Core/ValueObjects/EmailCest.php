<?php

declare(strict_types=1);

namespace Bgl\Tests\Unit\Core\ValueObjects;

use Bgl\Core\ValueObjects\Email;
use Bgl\Tests\Support\UnitTester;
use Codeception\Attribute\Group;

/**
 * @covers \Bgl\Core\ValueObjects\Email
 */
#[Group('core', 'value-object')]
final class EmailCest
{
    public function testValidEmail(UnitTester $i): void
    {
        $email = new Email('user@example.com');

        $i->assertSame('user@example.com', $email->getValue());
        $i->assertFalse($email->isNull());
    }

    public function testNullEmail(UnitTester $i): void
    {
        $email = new Email(null);

        $i->assertTrue($email->isNull());
        $i->assertNull($email->getValue());
    }

    public function testInvalidEmailThrows(UnitTester $i): void
    {
        $i->expectThrowable(
            \InvalidArgumentException::class,
            static function (): void {
                new Email('not-an-email');
            },
        );
    }

    public function testEmptyEmailThrows(UnitTester $i): void
    {
        $i->expectThrowable(
            \InvalidArgumentException::class,
            static function (): void {
                new Email('');
            },
        );
    }
}
