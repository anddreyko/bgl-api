<?php

declare(strict_types=1);

namespace Bgl\Tests\Unit\Domain\Profile\Entities;

use Bgl\Core\ValueObjects\Uuid;
use Bgl\Domain\Profile\Entities\EmailConfirmationToken;
use Bgl\Tests\Support\UnitTester;
use Codeception\Attribute\Group;

/**
 * @covers \Bgl\Domain\Profile\Entities\EmailConfirmationToken
 */
#[Group('auth', 'domain', 'entity')]
final class EmailConfirmationTokenCest
{
    public function testIsExpiredReturnsFalseWhenNotExpired(UnitTester $i): void
    {
        $expiresAt = new \DateTimeImmutable('2024-01-02 12:00:00');
        $now = new \DateTimeImmutable('2024-01-01 12:00:00');

        $token = EmailConfirmationToken::create(
            id: new Uuid('token-id'),
            userId: new Uuid('user-id'),
            token: 'some-token',
            expiresAt: $expiresAt,
        );

        $i->assertFalse($token->isExpired($now));
    }

    public function testIsExpiredReturnsTrueWhenExpired(UnitTester $i): void
    {
        $expiresAt = new \DateTimeImmutable('2024-01-01 12:00:00');
        $now = new \DateTimeImmutable('2024-01-02 12:00:00');

        $token = EmailConfirmationToken::create(
            id: new Uuid('token-id'),
            userId: new Uuid('user-id'),
            token: 'some-token',
            expiresAt: $expiresAt,
        );

        $i->assertTrue($token->isExpired($now));
    }

    public function testIsExpiredReturnsFalseWhenExactlyAtExpiry(UnitTester $i): void
    {
        $expiresAt = new \DateTimeImmutable('2024-01-01 12:00:00');
        $now = new \DateTimeImmutable('2024-01-01 12:00:00');

        $token = EmailConfirmationToken::create(
            id: new Uuid('token-id'),
            userId: new Uuid('user-id'),
            token: 'some-token',
            expiresAt: $expiresAt,
        );

        $i->assertFalse($token->isExpired($now));
    }
}
