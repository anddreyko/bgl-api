<?php

declare(strict_types=1);

namespace Bgl\Tests\Unit\Application\Handlers\Auth\Register;

use Bgl\Application\Handlers\Auth\Register\Command;
use Bgl\Application\Handlers\Auth\Register\Handler;
use Bgl\Core\Identity\UuidGenerator;
use Bgl\Core\Messages\Envelope;
use Bgl\Core\Security\PasswordHasher;
use Bgl\Core\ValueObjects\Email;
use Bgl\Core\ValueObjects\Uuid;
use Bgl\Domain\Auth\Entities\EmailConfirmationTokens;
use Bgl\Domain\Auth\Entities\User;
use Bgl\Domain\Auth\Entities\Users;
use Bgl\Domain\Auth\Exceptions\UserAlreadyExistsException;
use Bgl\Tests\Support\UnitTester;
use Codeception\Attribute\Group;
use Codeception\Stub;
use Psr\Clock\ClockInterface;

/**
 * @covers \Bgl\Application\Handlers\Auth\Register\Handler
 */
#[Group('auth', 'registration')]
final class HandlerCest
{
    public function testSuccessfulRegistration(UnitTester $i): void
    {
        $users = Stub::makeEmpty(Users::class, [
            'findByEmail' => static fn(): ?User => null,
            'add' => static function (): void {
            },
        ]);

        $tokens = Stub::makeEmpty(EmailConfirmationTokens::class, [
            'add' => static function (): void {
            },
        ]);

        $passwordHasher = Stub::makeEmpty(PasswordHasher::class, [
            'hash' => static fn(): string => 'hashed_password',
        ]);

        $uuidGenerator = Stub::makeEmpty(UuidGenerator::class, [
            'generate' => static fn(): Uuid => new Uuid('generated-uuid'),
        ]);

        $clock = Stub::makeEmpty(ClockInterface::class, [
            'now' => static fn(): \DateTimeImmutable => new \DateTimeImmutable('2024-01-01 12:00:00'),
        ]);

        $handler = new Handler($users, $tokens, $passwordHasher, $uuidGenerator, $clock);

        $command = new Command(email: 'test@example.com', password: 'secret123');
        $envelope = new Envelope($command, 'msg-1');

        $result = $handler($envelope);

        $i->assertSame('Confirm the specified email', $result);
    }

    public function testDuplicateEmailThrowsException(UnitTester $i): void
    {
        $existingUser = User::register(
            id: new Uuid('existing-user-id'),
            email: new Email(),
            passwordHash: 'hashed',
            createdAt: new \DateTimeImmutable('2024-01-01 12:00:00'),
        );

        $users = Stub::makeEmpty(Users::class, [
            'findByEmail' => static fn(): User => $existingUser,
        ]);

        $tokens = Stub::makeEmpty(EmailConfirmationTokens::class);

        $passwordHasher = Stub::makeEmpty(PasswordHasher::class);

        $uuidGenerator = Stub::makeEmpty(UuidGenerator::class);

        $clock = Stub::makeEmpty(ClockInterface::class, [
            'now' => static fn(): \DateTimeImmutable => new \DateTimeImmutable('2024-01-01 12:00:00'),
        ]);

        $handler = new Handler($users, $tokens, $passwordHasher, $uuidGenerator, $clock);

        $command = new Command(email: 'existing@example.com', password: 'secret123');
        $envelope = new Envelope($command, 'msg-2');

        $i->expectThrowable(UserAlreadyExistsException::class, static function () use ($handler, $envelope): void {
            $handler($envelope);
        });
    }
}
