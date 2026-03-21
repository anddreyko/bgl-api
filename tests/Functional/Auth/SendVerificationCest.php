<?php

declare(strict_types=1);

namespace Bgl\Tests\Functional\Auth;

use Bgl\Application\Handlers\Auth\SendVerification\Command;
use Bgl\Application\Handlers\Auth\SendVerification\Handler;
use Bgl\Application\Handlers\Auth\SendVerification\Result;
use Bgl\Core\Auth\Verifier;
use Bgl\Core\Identity\UuidGenerator;
use Bgl\Core\Messages\Envelope;
use Bgl\Core\Notification\Notifier;
use Bgl\Core\ValueObjects\DateTime;
use Bgl\Core\ValueObjects\Email;
use Bgl\Domain\Profile\User;
use Bgl\Domain\Profile\Users;
use Bgl\Tests\Support\DiHelper;
use Bgl\Tests\Support\Dummy\FakeNotifier;
use Bgl\Tests\Support\Dummy\FakeVerifier;
use Bgl\Tests\Support\FunctionalTester;
use Codeception\Attribute\Group;

/**
 * @covers \Bgl\Application\Handlers\Auth\SendVerification\Handler
 */
#[Group('application', 'handler', 'auth', 'verification')]
final class SendVerificationCest
{
    private Handler $handler;
    private Users $users;
    private FakeVerifier $verifier;
    private FakeNotifier $notifier;
    private UuidGenerator $uuidGenerator;

    public function _before(): void
    {
        $container = DiHelper::container();

        /** @var Handler $handler */
        $this->handler = $container->get(Handler::class);

        /** @var Users $users */
        $this->users = $container->get(Users::class);

        $verifier = $container->get(Verifier::class);
        \assert($verifier instanceof FakeVerifier);
        $this->verifier = $verifier;

        $notifier = $container->get(Notifier::class);
        \assert($notifier instanceof FakeNotifier);
        $this->notifier = $notifier;

        /** @var UuidGenerator $uuidGenerator */
        $this->uuidGenerator = $container->get(UuidGenerator::class);

        $this->notifier->reset();
    }

    public function testSendsVerificationForPendingUser(FunctionalTester $i): void
    {
        $email = 'verify-' . uniqid() . '@test.local';
        $userId = $this->uuidGenerator->generate();

        $user = User::register(
            id: $userId,
            email: new Email($email),
            passwordHash: 'hashed',
            createdAt: new DateTime(),
        );
        $this->users->add($user);

        $result = ($this->handler)(new Envelope(
            message: new Command(email: $email),
            messageId: 'msg-1',
        ));

        $i->assertInstanceOf(Result::class, $result);
        $i->assertNotNull($this->verifier->getLastCredentials());
        $i->assertNotNull($this->notifier->getLastSent());
        $i->assertSame($email, $this->notifier->getLastSent()->to);
    }

    public function testSendsVerificationByUserIdFromIdentityMap(FunctionalTester $i): void
    {
        $email = 'verify-id-' . uniqid() . '@test.local';
        $userId = $this->uuidGenerator->generate();

        $user = User::register(
            id: $userId,
            email: new Email($email),
            passwordHash: 'hashed',
            createdAt: new DateTime(),
        );
        $this->users->add($user);

        $result = ($this->handler)(new Envelope(
            message: new Command(email: $email, userId: (string) $userId),
            messageId: 'msg-2',
        ));

        $i->assertInstanceOf(Result::class, $result);
        $i->assertNotNull($this->verifier->getLastCredentials());
        $i->assertNotNull($this->notifier->getLastSent());
    }

    public function testReturnsAcceptedForNonexistentEmail(FunctionalTester $i): void
    {
        $result = ($this->handler)(new Envelope(
            message: new Command(email: 'nobody-' . uniqid() . '@test.local'),
            messageId: 'msg-3',
        ));

        $i->assertInstanceOf(Result::class, $result);
        $i->assertNull($this->notifier->getLastSent());
    }

    public function testReturnsAcceptedForActiveUser(FunctionalTester $i): void
    {
        $email = 'active-' . uniqid() . '@test.local';
        $userId = $this->uuidGenerator->generate();

        $user = User::register(
            id: $userId,
            email: new Email($email),
            passwordHash: 'hashed',
            createdAt: new DateTime(),
        );
        $user->confirm();
        $this->users->add($user);

        $result = ($this->handler)(new Envelope(
            message: new Command(email: $email),
            messageId: 'msg-4',
        ));

        $i->assertInstanceOf(Result::class, $result);
        $i->assertNull($this->notifier->getLastSent());
    }
}
