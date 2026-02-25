<?php

declare(strict_types=1);

namespace Bgl\Tests\Functional\Auth;

use Bgl\Application\Handlers\Auth\Register\Command;
use Bgl\Application\Handlers\Auth\Register\Handler;
use Bgl\Core\Identity\UuidGenerator;
use Bgl\Core\Messages\Envelope;
use Bgl\Core\ValueObjects\Email;
use Bgl\Domain\Profile\Entities\User;
use Bgl\Domain\Profile\Entities\Users;
use Bgl\Domain\Profile\Exceptions\UserAlreadyExistsException;
use Bgl\Tests\Support\DiHelper;
use Bgl\Tests\Support\FunctionalTester;
use Codeception\Attribute\Group;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @covers \Bgl\Application\Handlers\Auth\Register\Handler
 */
#[Group('application', 'handler', 'auth', 'registration')]
final class RegisterCest
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

    public function testSuccessfulRegistration(FunctionalTester $i): void
    {
        $email = 'register-' . uniqid() . '@test.local';

        $result = ($this->handler)(new Envelope(
            message: new Command(email: $email, password: 'secret123'),
            messageId: 'msg-1',
        ));

        $this->em->flush();

        $i->assertSame('Confirm the specified email', $result);
        $i->assertNotNull($this->users->findByEmail($email));
    }

    public function testDuplicateEmailThrowsException(FunctionalTester $i): void
    {
        $email = 'existing-' . uniqid() . '@test.local';

        $user = User::register(
            id: $this->uuidGenerator->generate(),
            email: new Email($email),
            passwordHash: 'hashed',
            createdAt: new \DateTimeImmutable(),
        );
        $this->users->add($user);
        $this->em->flush();
        $this->em->clear();

        $i->expectThrowable(UserAlreadyExistsException::class, fn () => ($this->handler)(new Envelope(
            message: new Command(email: $email, password: 'secret123'),
            messageId: 'msg-2',
        )));
    }
}
