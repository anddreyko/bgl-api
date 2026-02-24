<?php

declare(strict_types=1);

namespace Bgl\Tests\Unit\Infrastructure\Auth;

use Bgl\Core\Auth\AuthenticationException;
use Bgl\Core\Auth\AuthPayload;
use Bgl\Core\Auth\EmailNotConfirmedException;
use Bgl\Core\Auth\InvalidCredentialsException;
use Bgl\Core\Auth\InvalidRefreshTokenException;
use Bgl\Core\Auth\TokenPair;
use Bgl\Core\Auth\UserNotActiveException;
use Bgl\Core\Security\PasswordHasher;
use Bgl\Core\Security\TokenGenerator;
use Bgl\Core\Security\TokenTtlConfig;
use Bgl\Core\ValueObjects\Email;
use Bgl\Core\ValueObjects\Uuid;
use Bgl\Domain\Auth\Entities\User;
use Bgl\Domain\Auth\Entities\Users;
use Bgl\Domain\Auth\Entities\UserStatus;
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
    private TokenTtlConfig $ttlConfig;

    public function _before(): void
    {
        $this->ttlConfig = new TokenTtlConfig(7200, 2592000);
    }

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

    // -- login --

    public function testLoginSuccessful(UnitTester $i): void
    {
        $user = $this->makeUser();

        $authenticator = new JwtAuthenticator(
            tokenGenerator: Stub::makeEmpty(TokenGenerator::class, [
                'generate' => Stub::consecutive('access-token', 'refresh-token'),
            ]),
            users: Stub::makeEmpty(Users::class, [
                'findByEmail' => static fn(): User => $user,
            ]),
            passwordHasher: Stub::makeEmpty(PasswordHasher::class, [
                'verify' => static fn(): bool => true,
            ]),
            tokenTtlConfig: $this->ttlConfig,
        );

        $result = $authenticator->login('test@example.com', 'secret123');

        $i->assertInstanceOf(TokenPair::class, $result);
        $i->assertSame('access-token', $result->accessToken);
        $i->assertSame('refresh-token', $result->refreshToken);
        $i->assertSame(7200, $result->expiresIn);
    }

    public function testLoginUserNotFoundThrows(UnitTester $i): void
    {
        $authenticator = new JwtAuthenticator(
            tokenGenerator: Stub::makeEmpty(TokenGenerator::class),
            users: Stub::makeEmpty(Users::class, [
                'findByEmail' => static fn(): ?User => null,
            ]),
            passwordHasher: Stub::makeEmpty(PasswordHasher::class),
            tokenTtlConfig: $this->ttlConfig,
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
            tokenGenerator: Stub::makeEmpty(TokenGenerator::class),
            users: Stub::makeEmpty(Users::class, [
                'findByEmail' => fn(): User => $this->makeUser(),
            ]),
            passwordHasher: Stub::makeEmpty(PasswordHasher::class, [
                'verify' => static fn(): bool => false,
            ]),
            tokenTtlConfig: $this->ttlConfig,
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
            tokenGenerator: Stub::makeEmpty(TokenGenerator::class),
            users: Stub::makeEmpty(Users::class, [
                'findByEmail' => fn(): User => $this->makeUser(UserStatus::Inactive),
            ]),
            passwordHasher: Stub::makeEmpty(PasswordHasher::class, [
                'verify' => static fn(): bool => true,
            ]),
            tokenTtlConfig: $this->ttlConfig,
        );

        $i->expectThrowable(
            new EmailNotConfirmedException(),
            static function () use ($authenticator): void {
                $authenticator->login('test@example.com', 'secret123');
            },
        );
    }

    // -- refresh --

    public function testRefreshSuccessful(UnitTester $i): void
    {
        $user = $this->makeUser();

        $authenticator = new JwtAuthenticator(
            tokenGenerator: Stub::makeEmpty(TokenGenerator::class, [
                'verify' => static fn(): array => [
                    'userId' => 'user-id-123',
                    'type' => 'refresh',
                    'tokenVersion' => 1,
                ],
                'generate' => Stub::consecutive('new-access', 'new-refresh'),
            ]),
            users: Stub::makeEmpty(Users::class, [
                'find' => static fn(): User => $user,
            ]),
            passwordHasher: Stub::makeEmpty(PasswordHasher::class),
            tokenTtlConfig: $this->ttlConfig,
        );

        $result = $authenticator->refresh('old-refresh-token');

        $i->assertInstanceOf(TokenPair::class, $result);
        $i->assertSame('new-access', $result->accessToken);
        $i->assertSame('new-refresh', $result->refreshToken);
        $i->assertSame(7200, $result->expiresIn);
    }

    public function testRefreshInvalidTokenThrows(UnitTester $i): void
    {
        $authenticator = new JwtAuthenticator(
            tokenGenerator: Stub::makeEmpty(TokenGenerator::class, [
                'verify' => static function (): never {
                    throw new \RuntimeException('Invalid token');
                },
            ]),
            users: Stub::makeEmpty(Users::class),
            passwordHasher: Stub::makeEmpty(PasswordHasher::class),
            tokenTtlConfig: $this->ttlConfig,
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
            tokenGenerator: Stub::makeEmpty(TokenGenerator::class, [
                'verify' => static fn(): array => [
                    'userId' => 'user-id-123',
                    'type' => 'access',
                ],
            ]),
            users: Stub::makeEmpty(Users::class),
            passwordHasher: Stub::makeEmpty(PasswordHasher::class),
            tokenTtlConfig: $this->ttlConfig,
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
            tokenGenerator: Stub::makeEmpty(TokenGenerator::class, [
                'verify' => static fn(): array => ['userId' => 'user-id-123'],
            ]),
            users: Stub::makeEmpty(Users::class),
            passwordHasher: Stub::makeEmpty(PasswordHasher::class),
            tokenTtlConfig: $this->ttlConfig,
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
            tokenGenerator: Stub::makeEmpty(TokenGenerator::class, [
                'verify' => static fn(): array => [
                    'userId' => 'nonexistent',
                    'type' => 'refresh',
                    'tokenVersion' => 1,
                ],
            ]),
            users: Stub::makeEmpty(Users::class, [
                'find' => static fn(): ?User => null,
            ]),
            passwordHasher: Stub::makeEmpty(PasswordHasher::class),
            tokenTtlConfig: $this->ttlConfig,
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
            tokenGenerator: Stub::makeEmpty(TokenGenerator::class, [
                'verify' => static fn(): array => [
                    'userId' => 'user-id-123',
                    'type' => 'refresh',
                    'tokenVersion' => 1,
                ],
            ]),
            users: Stub::makeEmpty(Users::class, [
                'find' => fn(): User => $this->makeUser(UserStatus::Inactive),
            ]),
            passwordHasher: Stub::makeEmpty(PasswordHasher::class),
            tokenTtlConfig: $this->ttlConfig,
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
            tokenGenerator: Stub::makeEmpty(TokenGenerator::class, [
                'verify' => static fn(): array => [
                    'userId' => 'user-id-123',
                    'type' => 'refresh',
                    'tokenVersion' => 5,
                ],
            ]),
            users: Stub::makeEmpty(Users::class, [
                'find' => fn(): User => $this->makeUser(),
            ]),
            passwordHasher: Stub::makeEmpty(PasswordHasher::class),
            tokenTtlConfig: $this->ttlConfig,
        );

        $i->expectThrowable(
            AuthenticationException::class,
            static function () use ($authenticator): void {
                $authenticator->refresh('old-refresh');
            },
        );
    }

    // -- revoke --

    public function testRevokeIncrementsTokenVersion(UnitTester $i): void
    {
        $user = $this->makeUser();
        $i->assertSame(1, $user->getTokenVersion());

        $authenticator = new JwtAuthenticator(
            tokenGenerator: Stub::makeEmpty(TokenGenerator::class),
            users: Stub::makeEmpty(Users::class, [
                'find' => static fn(): User => $user,
            ]),
            passwordHasher: Stub::makeEmpty(PasswordHasher::class),
            tokenTtlConfig: $this->ttlConfig,
        );

        $authenticator->revoke('user-id-123');

        $i->assertSame(2, $user->getTokenVersion());
    }

    public function testRevokeUserNotFoundThrows(UnitTester $i): void
    {
        $authenticator = new JwtAuthenticator(
            tokenGenerator: Stub::makeEmpty(TokenGenerator::class),
            users: Stub::makeEmpty(Users::class, [
                'find' => static fn(): ?User => null,
            ]),
            passwordHasher: Stub::makeEmpty(PasswordHasher::class),
            tokenTtlConfig: $this->ttlConfig,
        );

        $i->expectThrowable(
            new AuthenticationException('User not found'),
            static function () use ($authenticator): void {
                $authenticator->revoke('nonexistent');
            },
        );
    }

    // -- verify --

    public function testVerifySuccessful(UnitTester $i): void
    {
        $authenticator = new JwtAuthenticator(
            tokenGenerator: Stub::makeEmpty(TokenGenerator::class, [
                'verify' => static fn(): array => [
                    'userId' => 'user-id-123',
                    'type' => 'access',
                    'tokenVersion' => 1,
                ],
            ]),
            users: Stub::makeEmpty(Users::class, [
                'find' => fn(): User => $this->makeUser(),
            ]),
            passwordHasher: Stub::makeEmpty(PasswordHasher::class),
            tokenTtlConfig: $this->ttlConfig,
        );

        $result = $authenticator->verify('valid-access-token');

        $i->assertInstanceOf(AuthPayload::class, $result);
        $i->assertSame('user-id-123', $result->userId);
    }

    public function testVerifyInvalidTokenThrows(UnitTester $i): void
    {
        $authenticator = new JwtAuthenticator(
            tokenGenerator: Stub::makeEmpty(TokenGenerator::class, [
                'verify' => static function (): never {
                    throw new \RuntimeException('Token expired', 42);
                },
            ]),
            users: Stub::makeEmpty(Users::class),
            passwordHasher: Stub::makeEmpty(PasswordHasher::class),
            tokenTtlConfig: $this->ttlConfig,
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
            tokenGenerator: Stub::makeEmpty(TokenGenerator::class, [
                'verify' => static fn(): array => [
                    'userId' => 'user-id-123',
                    'type' => 'refresh',
                ],
            ]),
            users: Stub::makeEmpty(Users::class),
            passwordHasher: Stub::makeEmpty(PasswordHasher::class),
            tokenTtlConfig: $this->ttlConfig,
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
            tokenGenerator: Stub::makeEmpty(TokenGenerator::class, [
                'verify' => static fn(): array => [
                    'userId' => 'user-id-123',
                    'tokenVersion' => 1,
                ],
            ]),
            users: Stub::makeEmpty(Users::class, [
                'find' => fn(): User => $this->makeUser(),
            ]),
            passwordHasher: Stub::makeEmpty(PasswordHasher::class),
            tokenTtlConfig: $this->ttlConfig,
        );

        $result = $authenticator->verify('legacy-token');

        $i->assertSame('user-id-123', $result->userId);
    }

    public function testVerifyMissingUserIdThrows(UnitTester $i): void
    {
        $authenticator = new JwtAuthenticator(
            tokenGenerator: Stub::makeEmpty(TokenGenerator::class, [
                'verify' => static fn(): array => ['type' => 'access'],
            ]),
            users: Stub::makeEmpty(Users::class),
            passwordHasher: Stub::makeEmpty(PasswordHasher::class),
            tokenTtlConfig: $this->ttlConfig,
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
            tokenGenerator: Stub::makeEmpty(TokenGenerator::class, [
                'verify' => static fn(): array => [
                    'userId' => 'nonexistent',
                    'type' => 'access',
                    'tokenVersion' => 1,
                ],
            ]),
            users: Stub::makeEmpty(Users::class, [
                'find' => static fn(): ?User => null,
            ]),
            passwordHasher: Stub::makeEmpty(PasswordHasher::class),
            tokenTtlConfig: $this->ttlConfig,
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
            tokenGenerator: Stub::makeEmpty(TokenGenerator::class, [
                'verify' => static fn(): array => [
                    'userId' => 'user-id-123',
                    'type' => 'access',
                    'tokenVersion' => 1,
                ],
            ]),
            users: Stub::makeEmpty(Users::class, [
                'find' => fn(): User => $this->makeUser(UserStatus::Inactive),
            ]),
            passwordHasher: Stub::makeEmpty(PasswordHasher::class),
            tokenTtlConfig: $this->ttlConfig,
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
            tokenGenerator: Stub::makeEmpty(TokenGenerator::class, [
                'verify' => static fn(): array => [
                    'userId' => 'user-id-123',
                    'type' => 'access',
                    'tokenVersion' => 1,
                ],
            ]),
            users: Stub::makeEmpty(Users::class, [
                'find' => fn(): User => $this->makeUser(tokenVersion: 2),
            ]),
            passwordHasher: Stub::makeEmpty(PasswordHasher::class),
            tokenTtlConfig: $this->ttlConfig,
        );

        $i->expectThrowable(
            AuthenticationException::class,
            static function () use ($authenticator): void {
                $authenticator->verify('old-token');
            },
        );
    }
}
