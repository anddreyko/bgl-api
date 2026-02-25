<?php

declare(strict_types=1);

namespace Bgl\Tests\Unit\Infrastructure\Auth;

use Bgl\Core\Auth\AuthenticationException;
use Bgl\Core\Auth\TokenPair;
use Bgl\Core\Security\Tokenizer;
use Bgl\Core\Security\TokenConfig;
use Bgl\Core\ValueObjects\Email;
use Bgl\Core\ValueObjects\Uuid;
use Bgl\Domain\Profile\Entities\User;
use Bgl\Domain\Profile\Entities\Users;
use Bgl\Domain\Profile\Entities\UserStatus;
use Bgl\Infrastructure\Auth\JwtTokenIssuer;
use Bgl\Tests\Support\UnitTester;
use Codeception\Attribute\Group;
use Codeception\Stub;

/**
 * @covers \Bgl\Infrastructure\Auth\JwtTokenIssuer
 */
#[Group('auth')]
final class JwtTokenIssuerCest
{
    private TokenConfig $tokenConfig;

    public function _before(): void
    {
        $this->tokenConfig = new TokenConfig(7200, 2592000);
    }

    private function makeUser(): User
    {
        return new User(
            id: new Uuid('user-id-123'),
            email: new Email('test@example.com'),
            passwordHash: 'hashed_password',
            createdAt: new \DateTimeImmutable('2024-01-01 12:00:00'),
            status: UserStatus::Active,
        );
    }

    public function testIssueReturnsTokenPair(UnitTester $i): void
    {
        $issuer = new JwtTokenIssuer(
            tokenizer: Stub::makeEmpty(Tokenizer::class, [
                'generate' => Stub::consecutive('access-token', 'refresh-token'),
            ]),
            users: Stub::makeEmpty(Users::class, [
                'find' => fn(): User => $this->makeUser(),
            ]),
            tokenConfig: $this->tokenConfig,
        );

        $result = $issuer->issue('user-id-123');

        $i->assertSame('access-token', $result->accessToken);
        $i->assertSame('refresh-token', $result->refreshToken);
        $i->assertSame(7200, $result->expiresIn);
    }

    public function testIssueUserNotFoundThrows(UnitTester $i): void
    {
        $issuer = new JwtTokenIssuer(
            tokenizer: Stub::makeEmpty(Tokenizer::class),
            users: Stub::makeEmpty(Users::class, [
                'find' => static fn(): ?User => null,
            ]),
            tokenConfig: $this->tokenConfig,
        );

        $i->expectThrowable(
            AuthenticationException::class,
            static function () use ($issuer): void {
                $issuer->issue('nonexistent');
            },
        );
    }
}
