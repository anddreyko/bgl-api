<?php

declare(strict_types=1);

namespace Bgl\Tests\Unit\Application\Handlers\Auth\Register;

use Bgl\Application\Handlers\Auth\Register\Command;
use Bgl\Application\Handlers\Auth\Register\Handler;
use Bgl\Core\Auth\Confirmer;
use Bgl\Core\Identity\UuidGenerator;
use Bgl\Core\Messages\Envelope;
use Bgl\Core\Security\Hasher;
use Bgl\Core\ValueObjects\Email;
use Bgl\Core\ValueObjects\Uuid;
use Bgl\Domain\Profile\Entities\User;
use Bgl\Domain\Profile\Entities\Users;
use Bgl\Domain\Profile\Exceptions\UserAlreadyExistsException;
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

        $confirmer = Stub::makeEmpty(Confirmer::class, [
            'request' => static function (): void {
            },
        ]);

        $passwordHasher = Stub::makeEmpty(Hasher::class, [
            'hash' => static fn(): string => 'hashed_password',
        ]);

        $uuidGenerator = Stub::makeEmpty(UuidGenerator::class, [
            'generate' => static fn(): Uuid => new Uuid('generated-uuid'),
        ]);

        $clock = Stub::makeEmpty(ClockInterface::class, [
            'now' => static fn(): \DateTimeImmutable => new \DateTimeImmutable('2024-01-01 12:00:00'),
        ]);

        $handler = new Handler($users, $confirmer, $passwordHasher, $uuidGenerator, $clock);

        $command = new Command(email: 'test@example.com', password: 'secret123');
        $envelope = new Envelope($command, 'msg-1');

        $result = $handler($envelope);

        $i->assertSame('Confirm the specified email', $result);
    }

    public function testDuplicateEmailThrowsException(UnitTester $i): void
    {
        $existingUser = User::register(
            id: new Uuid('existing-user-id'),
            email: new Email('existing@example.com'),
            passwordHash: 'hashed',
            createdAt: new \DateTimeImmutable('2024-01-01 12:00:00'),
        );

        $users = Stub::makeEmpty(Users::class, [
            'findByEmail' => static fn(): User => $existingUser,
        ]);

        $confirmer = Stub::makeEmpty(Confirmer::class);

        $passwordHasher = Stub::makeEmpty(Hasher::class);

        $uuidGenerator = Stub::makeEmpty(UuidGenerator::class);

        $clock = Stub::makeEmpty(ClockInterface::class, [
            'now' => static fn(): \DateTimeImmutable => new \DateTimeImmutable('2024-01-01 12:00:00'),
        ]);

        $handler = new Handler($users, $confirmer, $passwordHasher, $uuidGenerator, $clock);

        $command = new Command(email: 'existing@example.com', password: 'secret123');
        $envelope = new Envelope($command, 'msg-2');

        $i->expectThrowable(UserAlreadyExistsException::class, static function () use ($handler, $envelope): void {
            $handler($envelope);
        });
    }

    public function testRegistrationWithName(UnitTester $i): void
    {
        $users = Stub::makeEmpty(Users::class, [
            'findByEmail' => static fn(): ?User => null,
            'add' => static function (): void {
            },
        ]);

        $confirmer = Stub::makeEmpty(Confirmer::class, [
            'request' => static function (): void {
            },
        ]);

        $passwordHasher = Stub::makeEmpty(Hasher::class, [
            'hash' => static fn(): string => 'hashed_password',
        ]);

        $uuidGenerator = Stub::makeEmpty(UuidGenerator::class, [
            'generate' => static fn(): Uuid => new Uuid('generated-uuid'),
        ]);

        $clock = Stub::makeEmpty(ClockInterface::class, [
            'now' => static fn(): \DateTimeImmutable => new \DateTimeImmutable('2024-01-01 12:00:00'),
        ]);

        $handler = new Handler($users, $confirmer, $passwordHasher, $uuidGenerator, $clock);

        $command = new Command(email: 'test@example.com', password: 'secret123', name: 'Bob');
        $envelope = new Envelope($command, 'msg-3');

        $result = $handler($envelope);

        $i->assertSame('Confirm the specified email', $result);
    }
}
