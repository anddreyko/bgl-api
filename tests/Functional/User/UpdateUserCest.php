<?php

declare(strict_types=1);

namespace Bgl\Tests\Functional\User;

use Bgl\Application\Handlers\User\UpdateUser\Command;
use Bgl\Application\Handlers\User\UpdateUser\Handler;
use Bgl\Application\Handlers\User\UpdateUser\Result;
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
 * @covers \Bgl\Application\Handlers\User\UpdateUser\Handler
 */
#[Group('application', 'handler', 'user', 'user-update')]
final class UpdateUserCest
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

    public function testSuccessfulNameUpdate(FunctionalTester $i): void
    {
        $userId = $this->uuidGenerator->generate();
        $email = 'update-user-' . uniqid() . '@test.local';

        $user = new User(
            id: $userId,
            email: new Email($email),
            passwordHash: 'hashed_password',
            createdAt: new DateTime('2024-01-15 10:30:00'),
            status: UserStatus::Active,
            name: 'OldName',
        );
        $this->users->add($user);
        $this->em->flush();
        $this->em->clear();

        $result = ($this->handler)(new Envelope(
            message: new Command(
                userId: (string) $userId,
                name: 'NewName',
            ),
            messageId: 'msg-update-1',
        ));

        $i->assertInstanceOf(Result::class, $result);
        $i->assertSame((string) $userId, $result->id);
        $i->assertSame('NewName', $result->name);
        $i->assertSame($email, $result->email);
        $i->assertTrue($result->isActive);
    }

    public function testUserNotFoundThrows(FunctionalTester $i): void
    {
        $i->expectThrowable(
            new NotFoundException('User not found'),
            fn () => ($this->handler)(new Envelope(
                message: new Command(
                    userId: (string) $this->uuidGenerator->generate(),
                    name: 'SomeName',
                ),
                messageId: 'msg-update-2',
            )),
        );
    }

    public function testNamePersistedAfterFlush(FunctionalTester $i): void
    {
        $userId = $this->uuidGenerator->generate();
        $email = 'persist-user-' . uniqid() . '@test.local';

        $user = new User(
            id: $userId,
            email: new Email($email),
            passwordHash: 'hashed_password',
            createdAt: new DateTime('2024-01-15 10:30:00'),
            status: UserStatus::Active,
            name: 'BeforeName',
        );
        $this->users->add($user);
        $this->em->flush();
        $this->em->clear();

        ($this->handler)(new Envelope(
            message: new Command(
                userId: (string) $userId,
                name: 'AfterName',
            ),
            messageId: 'msg-update-3',
        ));

        $this->em->flush();
        $this->em->clear();

        $reloaded = $this->users->find((string) $userId);
        $i->assertNotNull($reloaded);
        $i->assertSame('AfterName', $reloaded->getName());
    }
}
