<?php

declare(strict_types=1);

namespace Bgl\Tests\Functional\User;

use Bgl\Application\Handlers\User\GetUser\Handler;
use Bgl\Application\Handlers\User\GetUser\Query;
use Bgl\Application\Handlers\User\GetUser\Result;
use Bgl\Core\Exceptions\NotFoundException;
use Bgl\Core\Identity\UuidGenerator;
use Bgl\Core\Messages\Envelope;
use Bgl\Core\ValueObjects\DateTime;
use Bgl\Core\ValueObjects\Email;
use Bgl\Domain\Profile\User;
use Bgl\Domain\Profile\Users;
use Bgl\Domain\Profile\UserStatus;
use Bgl\Tests\Support\DiHelper;
use Bgl\Tests\Support\FunctionalTester;
use Codeception\Attribute\Group;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @covers \Bgl\Application\Handlers\User\GetUser\Handler
 */
#[Group('application', 'handler', 'user', 'user-info')]
final class GetUserCest
{
    private EntityManagerInterface $em;
    private Handler $handler;
    private Users $users;
    private UuidGenerator $uuidGenerator;

    public function _before(): void
    {
        $container = DiHelper::container();

        /** @var EntityManagerInterface $em */
        $this->em = $container->get(EntityManagerInterface::class);

        /** @var Handler $handler */
        $this->handler = $container->get(Handler::class);

        /** @var Users $users */
        $this->users = $container->get(Users::class);

        /** @var UuidGenerator $uuidGenerator */
        $this->uuidGenerator = $container->get(UuidGenerator::class);
    }

    public function testSuccessfulUserRetrieval(FunctionalTester $i): void
    {
        $userId = $this->uuidGenerator->generate();
        $email = 'user-' . uniqid() . '@test.local';

        $user = new User(
            id: $userId,
            email: new Email($email),
            passwordHash: 'hashed_password',
            createdAt: new DateTime('2024-01-15 10:30:00'),
            status: UserStatus::Active,
        );
        $this->users->add($user);
        $this->em->flush();
        $this->em->clear();

        $result = ($this->handler)(new Envelope(
            message: new Query(userId: (string) $userId),
            messageId: 'msg-1',
        ));

        $i->assertInstanceOf(Result::class, $result);
        $i->assertSame((string) $userId, $result->id);
        $i->assertSame($email, $result->email);
        $i->assertTrue($result->isActive);
    }

    public function testUserNotFoundThrowsDomainException(FunctionalTester $i): void
    {
        $i->expectThrowable(
            new NotFoundException('User not found'),
            fn () => ($this->handler)(new Envelope(
                message: new Query(userId: 'nonexistent-' . uniqid()),
                messageId: 'msg-2',
            )),
        );
    }
}
