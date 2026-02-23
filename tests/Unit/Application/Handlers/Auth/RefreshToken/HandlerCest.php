<?php

declare(strict_types=1);

namespace Bgl\Tests\Unit\Application\Handlers\Auth\RefreshToken;

use Bgl\Application\Handlers\Auth\RefreshToken\Command;
use Bgl\Application\Handlers\Auth\RefreshToken\Handler;
use Bgl\Application\Handlers\Auth\RefreshToken\Result;
use Bgl\Core\Messages\Envelope;
use Bgl\Core\Security\TokenGenerator;
use Bgl\Core\ValueObjects\Email;
use Bgl\Core\ValueObjects\Uuid;
use Bgl\Domain\Auth\Entities\User;
use Bgl\Domain\Auth\Entities\Users;
use Bgl\Domain\Auth\Entities\UserStatus;
use Bgl\Tests\Support\UnitTester;
use Codeception\Attribute\Group;
use Codeception\Stub;

/**
 * @covers \Bgl\Application\Handlers\Auth\RefreshToken\Handler
 */
#[Group('auth', 'token-refresh')]
final class HandlerCest
{
    public function testSuccessfulRefresh(UnitTester $i): void
    {
        $user = new User(
            id: new Uuid('user-id-123'),
            email: new Email('test@example.com'),
            passwordHash: 'hashed_password',
            createdAt: new \DateTimeImmutable('2024-01-01 12:00:00'),
            status: UserStatus::Active,
        );

        $tokenGenerator = Stub::makeEmpty(TokenGenerator::class, [
            'verify' => static fn(): array => ['userId' => 'user-id-123', 'type' => 'refresh'],
            'generate' => Stub::consecutive('new-access-token', 'new-refresh-token'),
        ]);

        $users = Stub::makeEmpty(Users::class, [
            'find' => static fn(): User => $user,
        ]);

        $handler = new Handler($tokenGenerator, $users);

        $command = new Command(refreshToken: 'old-refresh-token');
        $envelope = new Envelope($command, 'msg-1');

        $result = $handler($envelope);

        $i->assertInstanceOf(Result::class, $result);
        $i->assertSame('new-access-token', $result->accessToken);
        $i->assertSame('new-refresh-token', $result->refreshToken);
        $i->assertSame(7200, $result->expiresIn);
    }

    public function testInvalidTokenThrowsDomainException(UnitTester $i): void
    {
        $tokenGenerator = Stub::makeEmpty(TokenGenerator::class, [
            'verify' => static fn(): array => ['invalid' => 'payload'],
        ]);

        $users = Stub::makeEmpty(Users::class);

        $handler = new Handler($tokenGenerator, $users);

        $command = new Command(refreshToken: 'invalid-token');
        $envelope = new Envelope($command, 'msg-2');

        $i->expectThrowable(
            new \DomainException('Invalid refresh token'),
            static function () use ($handler, $envelope): void {
                $handler($envelope);
            },
        );
    }

    public function testWrongTokenTypeThrowsDomainException(UnitTester $i): void
    {
        $tokenGenerator = Stub::makeEmpty(TokenGenerator::class, [
            'verify' => static fn(): array => ['userId' => 'user-id-123', 'type' => 'access'],
        ]);

        $users = Stub::makeEmpty(Users::class);

        $handler = new Handler($tokenGenerator, $users);

        $command = new Command(refreshToken: 'access-token-instead');
        $envelope = new Envelope($command, 'msg-3');

        $i->expectThrowable(
            new \DomainException('Invalid token type'),
            static function () use ($handler, $envelope): void {
                $handler($envelope);
            },
        );
    }

    public function testUserNotFoundThrowsDomainException(UnitTester $i): void
    {
        $tokenGenerator = Stub::makeEmpty(TokenGenerator::class, [
            'verify' => static fn(): array => ['userId' => 'nonexistent-id', 'type' => 'refresh'],
        ]);

        $users = Stub::makeEmpty(Users::class, [
            'find' => static fn(): ?User => null,
        ]);

        $handler = new Handler($tokenGenerator, $users);

        $command = new Command(refreshToken: 'valid-refresh-token');
        $envelope = new Envelope($command, 'msg-4');

        $i->expectThrowable(
            new \DomainException('User not found'),
            static function () use ($handler, $envelope): void {
                $handler($envelope);
            },
        );
    }

    public function testInactiveUserThrowsDomainException(UnitTester $i): void
    {
        $user = new User(
            id: new Uuid('user-id-123'),
            email: new Email('test@example.com'),
            passwordHash: 'hashed_password',
            createdAt: new \DateTimeImmutable('2024-01-01 12:00:00'),
            status: UserStatus::Inactive,
        );

        $tokenGenerator = Stub::makeEmpty(TokenGenerator::class, [
            'verify' => static fn(): array => ['userId' => 'user-id-123', 'type' => 'refresh'],
        ]);

        $users = Stub::makeEmpty(Users::class, [
            'find' => static fn(): User => $user,
        ]);

        $handler = new Handler($tokenGenerator, $users);

        $command = new Command(refreshToken: 'valid-refresh-token');
        $envelope = new Envelope($command, 'msg-5');

        $i->expectThrowable(
            new \DomainException('User is not active'),
            static function () use ($handler, $envelope): void {
                $handler($envelope);
            },
        );
    }
}
