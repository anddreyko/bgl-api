<?php

declare(strict_types=1);

namespace Bgl\Tests\Unit\Core\Auth;

use Bgl\Core\Auth\GrantType;
use Bgl\Tests\Support\UnitTester;
use Codeception\Attribute\Group;

/**
 * @covers \Bgl\Core\Auth\GrantType
 */
#[Group('core', 'auth', 'grantType')]
final class GrantTypeCest
{
    public function testPasskeyValue(UnitTester $i): void
    {
        $i->assertSame('passkey', GrantType::Passkey->value);
    }

    public function testCredentialValue(UnitTester $i): void
    {
        $i->assertSame('credential', GrantType::Credential->value);
    }

    public function testFromStringPasskey(UnitTester $i): void
    {
        $i->assertSame(GrantType::Passkey, GrantType::from('passkey'));
    }

    public function testFromStringCredential(UnitTester $i): void
    {
        $i->assertSame(GrantType::Credential, GrantType::from('credential'));
    }

    public function testTryFromInvalidReturnsNull(UnitTester $i): void
    {
        $i->assertNull(GrantType::tryFrom('invalid'));
    }

    public function testCasesCount(UnitTester $i): void
    {
        $cases = GrantType::cases();

        $i->assertCount(2, $cases);
    }

    public function testCasesContainsExpectedValues(UnitTester $i): void
    {
        $cases = GrantType::cases();

        $i->assertContains(GrantType::Passkey, $cases);
        $i->assertContains(GrantType::Credential, $cases);
    }
}
