<?php

declare(strict_types=1);

namespace Bgl\Tests\Unit\Domain\Profile\Entities;

use Bgl\Core\ValueObjects\DateTime;
use Bgl\Core\ValueObjects\Uuid;
use Bgl\Domain\Profile\Passkey\PasskeyChallenge;
use Bgl\Tests\Support\UnitTester;
use Codeception\Attribute\Group;

/**
 * @covers \Bgl\Domain\Profile\Passkey\PasskeyChallenge
 */
#[Group('auth', 'domain', 'entity')]
final class PasskeyChallengeCest
{
    public function testForRegistrationSetsAllFields(UnitTester $i): void
    {
        $id = new Uuid('challenge-id-1');
        $userId = new Uuid('user-id-1');
        $expiresAt = new DateTime('2026-01-15 10:05:00');

        $challenge = PasskeyChallenge::forRegistration(
            id: $id,
            challenge: 'random-challenge-bytes',
            expiresAt: $expiresAt,
            userId: $userId,
        );

        $i->assertSame($id, $challenge->getId());
        $i->assertSame('random-challenge-bytes', $challenge->getChallenge());
        $i->assertSame($expiresAt, $challenge->getExpiresAt());
        $i->assertSame($userId, $challenge->getUserId());
    }

    public function testForLoginHasNullUserId(UnitTester $i): void
    {
        $challenge = PasskeyChallenge::forLogin(
            id: new Uuid('challenge-id-2'),
            challenge: 'login-challenge',
            expiresAt: new DateTime('2026-01-15 10:05:00'),
        );

        $i->assertNull($challenge->getUserId());
    }

    public function testIsExpiredReturnsFalseBeforeExpiry(UnitTester $i): void
    {
        $challenge = PasskeyChallenge::forLogin(
            id: new Uuid('challenge-id-3'),
            challenge: 'test',
            expiresAt: new DateTime('2026-01-15 10:05:00'),
        );

        $now = new DateTime('2026-01-15 10:04:59');

        $i->assertFalse($challenge->isExpired($now));
    }

    public function testIsExpiredReturnsTrueAtExpiry(UnitTester $i): void
    {
        $expiresAt = new DateTime('2026-01-15 10:05:00');

        $challenge = PasskeyChallenge::forLogin(
            id: new Uuid('challenge-id-4'),
            challenge: 'test',
            expiresAt: $expiresAt,
        );

        $i->assertTrue($challenge->isExpired($expiresAt));
    }

    public function testIsExpiredReturnsTrueAfterExpiry(UnitTester $i): void
    {
        $challenge = PasskeyChallenge::forLogin(
            id: new Uuid('challenge-id-5'),
            challenge: 'test',
            expiresAt: new DateTime('2026-01-15 10:05:00'),
        );

        $now = new DateTime('2026-01-15 10:06:00');

        $i->assertTrue($challenge->isExpired($now));
    }
}
