<?php

declare(strict_types=1);

namespace Bgl\Tests\Unit\Infrastructure\Auth;

use Bgl\Core\Auth\AuthenticationException;
use Bgl\Core\Auth\EmailNotConfirmedException;
use Bgl\Core\Auth\InvalidCredentialsException;
use Bgl\Core\Auth\InvalidRefreshTokenException;
use Bgl\Core\Auth\TokenIssuer;
use Bgl\Core\Auth\TokenPair;
use Bgl\Core\Auth\UserNotActiveException;
use Bgl\Core\Security\Hasher;
use Bgl\Core\Security\Tokenizer;
use Bgl\Core\ValueObjects\Email;
use Bgl\Core\ValueObjects\Uuid;
use Bgl\Domain\Profile\Entities\User;
use Bgl\Domain\Profile\Entities\Users;
use Bgl\Domain\Profile\Entities\UserStatus;
use Bgl\Infrastructure\Auth\JwtAuthenticator;
use Bgl\Tests\Support\UnitTester;
use Codeception\Attribute\Group;
use Codeception\Stub;

/**
 * @covers \Bgl\Infrastructure\Auth\JwtAuthenticator
 */
#[Group('auth')]
final class JwtAuthenticatorCest
{
    private function makeUser(UserStatus $status = UserStatus::Active, int $tokenVersion = 1): User
    {
        $user = new User(
            id: new Uuid('user-id-123'),
            email: new Email('test@example.com'),
            passwordHash: 'hashed_password',
            createdAt: new \DateTimeImmutable('2024-01-01 12:00:00'),
            status: $status,
        );

        for ($v = 1; $v < $tokenVersion; $v++) {
            $user->incrementTokenVersion();
        }

        return $user;
    }

    private function makeTokenIssuer(): TokenIssuer
    {
        return Stub::makeEmpty(TokenIssuer::class, [
            'issue' => static fn(): TokenPair => new TokenPair('access-token', 'refresh-token', 7200),
        ]);
    }

    public function testLoginSuccessful(UnitTester $i): void
    {
        $user = $this->makeUser();

        $authenticator = new JwtAuthenticator(
            tokenizer: Stub::makeEmpty(Tokenizer::class),
            users: Stub::makeEmpty(Users::class, [
                'findByEmail' => static fn(): User => $user,
            ]),
            passwordHasher: Stub::makeEmpty(Hasher::class, [
                'verify' => static fn(): bool => true,
            ]),
            tokenIssuer: $this->makeTokenIssuer(),
        );

        $result = $authenticator->login('test@example.com', 'secret123');

        $i->assertSame('access-token', $result->accessToken);
        $i->assertSame('refresh-token', $result->refreshToken);
        $i->assertSame(7200, $result->expiresIn);
    }

    public function testLoginUserNotFoundThrows(UnitTester $i): void
    {
        $authenticator = new JwtAuthenticator(
            tokenizer: Stub::makeEmpty(Tokenizer::class),
            users: Stub::makeEmpty(Users::class, [
                'findByEmail' => static fn(): ?User => null,
            ]),
            passwordHasher: Stub::makeEmpty(Hasher::class),
            tokenIssuer: $this->makeTokenIssuer(),
        );

        $i->expectThrowable(
            new InvalidCredentialsException(),
            static function () use ($authenticator): void {
                $authenticator->login('nonexistent@example.com', 'secret123');
            },
        );
    }

    public function testLoginWrongPasswordThrows(UnitTester $i): void
    {
        $authenticator = new JwtAuthenticator(
            tokenizer: Stub::makeEmpty(Tokenizer::class),
            users: Stub::makeEmpty(Users::class, [
                'findByEmail' => fn(): User => $this->makeUser(),
            ]),
            passwordHasher: Stub::makeEmpty(Hasher::class, [
                'verify' => static fn(): bool => false,
            ]),
            tokenIssuer: $this->makeTokenIssuer(),
        );

        $i->expectThrowable(
            new InvalidCredentialsException(),
            static function () use ($authenticator): void {
                $authenticator->login('test@example.com', 'wrong');
            },
        );
    }

    public function testLoginInactiveUserThrows(UnitTester $i): void
    {
        $authenticator = new JwtAuthenticator(
            tokenizer: Stub::makeEmpty(Tokenizer::class),
            users: Stub::makeEmpty(Users::class, [
                'findByEmail' => fn(): User => $this->makeUser(UserStatus::Inactive),
            ]),
            passwordHasher: Stub::makeEmpty(Hasher::class, [
                'verify' => static fn(): bool => true,
            ]),
            tokenIssuer: $this->makeTokenIssuer(),
        );

        $i->expectThrowable(
            new EmailNotConfirmedException(),
            static function () use ($authenticator): void {
                $authenticator->login('test@example.com', 'secret123');
            },
        );
    }

    public function testRefreshSuccessful(UnitTester $i): void
    {
        $user = $this->makeUser();

        $authenticator = new JwtAuthenticator(
            tokenizer: Stub::makeEmpty(Tokenizer::class, [
                'verify' => static fn(): array => [
                    'userId' => 'user-id-123',
                    'type' => 'refresh',
                    'tokenVersion' => 1,
                ],
            ]),
            users: Stub::makeEmpty(Users::class, [
                'find' => static fn(): User => $user,
            ]),
            passwordHasher: Stub::makeEmpty(Hasher::class),
            tokenIssuer: $this->makeTokenIssuer(),
        );

        $result = $authenticator->refresh('old-refresh-token');

        $i->assertSame('access-token', $result->accessToken);
        $i->assertSame('refresh-token', $result->refreshToken);
        $i->assertSame(7200, $result->expiresIn);
    }

    public function testRefreshInvalidTokenThrows(UnitTester $i): void
    {
        $authenticator = new JwtAuthenticator(
            tokenizer: Stub::makeEmpty(Tokenizer::class, [
                'verify' => static function (): never {
                    throw new \RuntimeException('Invalid token');
                },
            ]),
            users: Stub::makeEmpty(Users::class),
            passwordHasher: Stub::makeEmpty(Hasher::class),
            tokenIssuer: $this->makeTokenIssuer(),
        );

        $i->expectThrowable(
            AuthenticationException::class,
            static function () use ($authenticator): void {
                $authenticator->refresh('garbage');
            },
        );
    }

    public function testRefreshWrongTypeThrows(UnitTester $i): void
    {
        $authenticator = new JwtAuthenticator(
            tokenizer: Stub::makeEmpty(Tokenizer::class, [
                'verify' => static fn(): array => [
                    'userId' => 'user-id-123',
                    'type' => 'access',
                ],
            ]),
            users: Stub::makeEmpty(Users::class),
            passwordHasher: Stub::makeEmpty(Hasher::class),
            tokenIssuer: $this->makeTokenIssuer(),
        );

        $i->expectThrowable(
            new InvalidRefreshTokenException(),
            static function () use ($authenticator): void {
                $authenticator->refresh('access-token-instead');
            },
        );
    }

    public function testRefreshMissingTypeThrows(UnitTester $i): void
    {
        $authenticator = new JwtAuthenticator(
            tokenizer: Stub::makeEmpty(Tokenizer::class, [
                'verify' => static fn(): array => ['userId' => 'user-id-123'],
            ]),
            users: Stub::makeEmpty(Users::class),
            passwordHasher: Stub::makeEmpty(Hasher::class),
            tokenIssuer: $this->makeTokenIssuer(),
        );

        $i->expectThrowable(
            new InvalidRefreshTokenException(),
            static function () use ($authenticator): void {
                $authenticator->refresh('token-no-type');
            },
        );
    }

    public function testRefreshUserNotFoundThrows(UnitTester $i): void
    {
        $authenticator = new JwtAuthenticator(
            tokenizer: Stub::makeEmpty(Tokenizer::class, [
                'verify' => static fn(): array => [
                    'userId' => 'nonexistent',
                    'type' => 'refresh',
                    'tokenVersion' => 1,
                ],
            ]),
            users: Stub::makeEmpty(Users::class, [
                'find' => static fn(): ?User => null,
            ]),
            passwordHasher: Stub::makeEmpty(Hasher::class),
            tokenIssuer: $this->makeTokenIssuer(),
        );

        $i->expectThrowable(
            AuthenticationException::class,
            static function () use ($authenticator): void {
                $authenticator->refresh('valid-refresh');
            },
        );
    }

    public function testRefreshInactiveUserThrows(UnitTester $i): void
    {
        $authenticator = new JwtAuthenticator(
            tokenizer: Stub::makeEmpty(Tokenizer::class, [
                'verify' => static fn(): array => [
                    'userId' => 'user-id-123',
                    'type' => 'refresh',
                    'tokenVersion' => 1,
                ],
            ]),
            users: Stub::makeEmpty(Users::class, [
                'find' => fn(): User => $this->makeUser(UserStatus::Inactive),
            ]),
            passwordHasher: Stub::makeEmpty(Hasher::class),
            tokenIssuer: $this->makeTokenIssuer(),
        );

        $i->expectThrowable(
            new UserNotActiveException(),
            static function () use ($authenticator): void {
                $authenticator->refresh('valid-refresh');
            },
        );
    }

    public function testRefreshTokenVersionMismatchThrows(UnitTester $i): void
    {
        $authenticator = new JwtAuthenticator(
            tokenizer: Stub::makeEmpty(Tokenizer::class, [
                'verify' => static fn(): array => [
                    'userId' => 'user-id-123',
                    'type' => 'refresh',
                    'tokenVersion' => 5,
                ],
            ]),
            users: Stub::makeEmpty(Users::class, [
                'find' => fn(): User => $this->makeUser(),
            ]),
            passwordHasher: Stub::makeEmpty(Hasher::class),
            tokenIssuer: $this->makeTokenIssuer(),
        );

        $i->expectThrowable(
            AuthenticationException::class,
            static function () use ($authenticator): void {
                $authenticator->refresh('old-refresh');
            },
        );
    }

    public function testRevokeIncrementsTokenVersion(UnitTester $i): void
    {
        $user = $this->makeUser();
        $i->assertSame(1, $user->getTokenVersion());

        $authenticator = new JwtAuthenticator(
            tokenizer: Stub::makeEmpty(Tokenizer::class),
            users: Stub::makeEmpty(Users::class, [
                'find' => static fn(): User => $user,
            ]),
            passwordHasher: Stub::makeEmpty(Hasher::class),
            tokenIssuer: $this->makeTokenIssuer(),
        );

        $authenticator->revoke('user-id-123');

        $i->assertSame(2, $user->getTokenVersion());
    }

    public function testRevokeUserNotFoundThrows(UnitTester $i): void
    {
        $authenticator = new JwtAuthenticator(
            tokenizer: Stub::makeEmpty(Tokenizer::class),
            users: Stub::makeEmpty(Users::class, [
                'find' => static fn(): ?User => null,
            ]),
            passwordHasher: Stub::makeEmpty(Hasher::class),
            tokenIssuer: $this->makeTokenIssuer(),
        );

        $i->expectThrowable(
            new AuthenticationException('User not found'),
            static function () use ($authenticator): void {
                $authenticator->revoke('nonexistent');
            },
        );
    }

    public function testVerifySuccessful(UnitTester $i): void
    {
        $authenticator = new JwtAuthenticator(
            tokenizer: Stub::makeEmpty(Tokenizer::class, [
                'verify' => static fn(): array => [
                    'userId' => 'user-id-123',
                    'type' => 'access',
                    'tokenVersion' => 1,
                ],
            ]),
            users: Stub::makeEmpty(Users::class, [
                'find' => fn(): User => $this->makeUser(),
            ]),
            passwordHasher: Stub::makeEmpty(Hasher::class),
            tokenIssuer: $this->makeTokenIssuer(),
        );

        $result = $authenticator->verify('valid-access-token');

        $i->assertSame('user-id-123', $result->userId);
    }

    public function testVerifyInvalidTokenThrows(UnitTester $i): void
    {
        $authenticator = new JwtAuthenticator(
            tokenizer: Stub::makeEmpty(Tokenizer::class, [
                'verify' => static function (): never {
                    throw new \RuntimeException('Token expired', 42);
                },
            ]),
            users: Stub::makeEmpty(Users::class),
            passwordHasher: Stub::makeEmpty(Hasher::class),
            tokenIssuer: $this->makeTokenIssuer(),
        );

        try {
            $authenticator->verify('expired-token');
            $i->fail('Expected AuthenticationException was not thrown');
        } catch (AuthenticationException $e) {
            $i->assertSame('Token expired', $e->getMessage());
            $i->assertSame(42, $e->getCode());
        }
    }

    public function testVerifyRefreshTypeThrows(UnitTester $i): void
    {
        $authenticator = new JwtAuthenticator(
            tokenizer: Stub::makeEmpty(Tokenizer::class, [
                'verify' => static fn(): array => [
                    'userId' => 'user-id-123',
                    'type' => 'refresh',
                ],
            ]),
            users: Stub::makeEmpty(Users::class),
            passwordHasher: Stub::makeEmpty(Hasher::class),
            tokenIssuer: $this->makeTokenIssuer(),
        );

        $i->expectThrowable(
            AuthenticationException::class,
            static function () use ($authenticator): void {
                $authenticator->verify('refresh-token');
            },
        );
    }

    public function testVerifyTokenWithoutTypeIsAccepted(UnitTester $i): void
    {
        $authenticator = new JwtAuthenticator(
            tokenizer: Stub::makeEmpty(Tokenizer::class, [
                'verify' => static fn(): array => [
                    'userId' => 'user-id-123',
                    'tokenVersion' => 1,
                ],
            ]),
            users: Stub::makeEmpty(Users::class, [
                'find' => fn(): User => $this->makeUser(),
            ]),
            passwordHasher: Stub::makeEmpty(Hasher::class),
            tokenIssuer: $this->makeTokenIssuer(),
        );

        $result = $authenticator->verify('legacy-token');

        $i->assertSame('user-id-123', $result->userId);
    }

    public function testVerifyMissingUserIdThrows(UnitTester $i): void
    {
        $authenticator = new JwtAuthenticator(
            tokenizer: Stub::makeEmpty(Tokenizer::class, [
                'verify' => static fn(): array => ['type' => 'access'],
            ]),
            users: Stub::makeEmpty(Users::class),
            passwordHasher: Stub::makeEmpty(Hasher::class),
            tokenIssuer: $this->makeTokenIssuer(),
        );

        $i->expectThrowable(
            AuthenticationException::class,
            static function () use ($authenticator): void {
                $authenticator->verify('token-no-user');
            },
        );
    }

    public function testVerifyUserNotFoundThrows(UnitTester $i): void
    {
        $authenticator = new JwtAuthenticator(
            tokenizer: Stub::makeEmpty(Tokenizer::class, [
                'verify' => static fn(): array => [
                    'userId' => 'nonexistent',
                    'type' => 'access',
                    'tokenVersion' => 1,
                ],
            ]),
            users: Stub::makeEmpty(Users::class, [
                'find' => static fn(): ?User => null,
            ]),
            passwordHasher: Stub::makeEmpty(Hasher::class),
            tokenIssuer: $this->makeTokenIssuer(),
        );

        $i->expectThrowable(
            AuthenticationException::class,
            static function () use ($authenticator): void {
                $authenticator->verify('valid-token');
            },
        );
    }

    public function testVerifyInactiveUserThrows(UnitTester $i): void
    {
        $authenticator = new JwtAuthenticator(
            tokenizer: Stub::makeEmpty(Tokenizer::class, [
                'verify' => static fn(): array => [
                    'userId' => 'user-id-123',
                    'type' => 'access',
                    'tokenVersion' => 1,
                ],
            ]),
            users: Stub::makeEmpty(Users::class, [
                'find' => fn(): User => $this->makeUser(UserStatus::Inactive),
            ]),
            passwordHasher: Stub::makeEmpty(Hasher::class),
            tokenIssuer: $this->makeTokenIssuer(),
        );

        $i->expectThrowable(
            new UserNotActiveException(),
            static function () use ($authenticator): void {
                $authenticator->verify('valid-token');
            },
        );
    }

    public function testVerifyTokenVersionMismatchThrows(UnitTester $i): void
    {
        $authenticator = new JwtAuthenticator(
            tokenizer: Stub::makeEmpty(Tokenizer::class, [
                'verify' => static fn(): array => [
                    'userId' => 'user-id-123',
                    'type' => 'access',
                    'tokenVersion' => 1,
                ],
            ]),
            users: Stub::makeEmpty(Users::class, [
                'find' => fn(): User => $this->makeUser(tokenVersion: 2),
            ]),
            passwordHasher: Stub::makeEmpty(Hasher::class),
            tokenIssuer: $this->makeTokenIssuer(),
        );

        $i->expectThrowable(
            AuthenticationException::class,
            static function () use ($authenticator): void {
                $authenticator->verify('old-token');
            },
        );
    }
}
