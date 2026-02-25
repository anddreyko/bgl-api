<?php

declare(strict_types=1);

namespace Bgl\Tests\Unit\Application\Handlers\User\GetUser;

use Bgl\Application\Handlers\User\GetUser\Handler;
use Bgl\Application\Handlers\User\GetUser\Query;
use Bgl\Application\Handlers\User\GetUser\Result;
use Bgl\Core\Messages\Envelope;
use Bgl\Core\ValueObjects\Email;
use Bgl\Core\ValueObjects\Uuid;
use Bgl\Domain\Profile\Entities\User;
use Bgl\Domain\Profile\Entities\Users;
use Bgl\Domain\Profile\Entities\UserStatus;
use Bgl\Tests\Support\UnitTester;
use Codeception\Attribute\Group;
use Codeception\Stub;

/**
 * @covers \Bgl\Application\Handlers\User\GetUser\Handler
 */
#[Group('user', 'user-info')]
final class HandlerCest
{
    public function testSuccessfulUserRetrieval(UnitTester $i): void
    {
        $userId = 'user-id-123';
        $createdAt = new \DateTimeImmutable('2024-01-15 10:30:00');

        $user = new User(
            id: new Uuid($userId),
            email: new Email('user@example.com'),
            passwordHash: 'hashed_password',
            createdAt: $createdAt,
            status: UserStatus::Active,
        );

        $users = Stub::makeEmpty(Users::class, [
            'find' => static fn(): User => $user,
        ]);

        $handler = new Handler($users);

        $query = new Query(userId: $userId);
        $envelope = new Envelope($query, 'msg-1');

        $result = $handler($envelope);

        $i->assertInstanceOf(Result::class, $result);
        $i->assertSame($userId, $result->id);
        $i->assertSame('user@example.com', $result->email);
        $i->assertTrue($result->isActive);
        $i->assertSame('2024-01-15T10:30:00+00:00', $result->createdAt);
        $i->assertMatchesRegularExpression('/^User#\d{4}$/', (string)$result->name);
    }

    public function testUserNotFoundThrowsDomainException(UnitTester $i): void
    {
        $users = Stub::makeEmpty(Users::class, [
            'find' => static fn(): ?User => null,
        ]);

        $handler = new Handler($users);

        $query = new Query(userId: 'nonexistent-id');
        $envelope = new Envelope($query, 'msg-2');

        $i->expectThrowable(
            new \DomainException('User not found'),
            static function () use ($handler, $envelope): void {
                $handler($envelope);
            },
        );
    }
}
