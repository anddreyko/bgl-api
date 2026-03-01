<?php

declare(strict_types=1);

namespace Bgl\Tests\Functional\Auth;

use Bgl\Application\Handlers\Auth\ConfirmEmail\Command;
use Bgl\Application\Handlers\Auth\ConfirmEmail\Handler;
use Bgl\Application\Handlers\Auth\ConfirmEmail\Result;
use Bgl\Core\Auth\Confirmer;
use Bgl\Core\Auth\ExpiredConfirmationTokenException;
use Bgl\Core\Auth\InvalidConfirmationTokenException;
use Bgl\Core\Identity\UuidGenerator;
use Bgl\Core\Messages\Envelope;
use Bgl\Core\ValueObjects\DateTime;
use Bgl\Core\ValueObjects\Email;
use Bgl\Domain\Profile\User;
use Bgl\Domain\Profile\Users;
use Bgl\Domain\Profile\UserStatus;
use Bgl\Tests\Support\DiHelper;
use Bgl\Tests\Support\Dummy\FakeConfirmer;
use Bgl\Tests\Support\FunctionalTester;
use Codeception\Attribute\Group;

/**
 * @covers \Bgl\Application\Handlers\Auth\ConfirmEmail\Handler
 */
#[Group('application', 'handler', 'auth', 'confirmation')]
final class ConfirmEmailCest
{
    private Handler $handler;
    private Users $users;
    private FakeConfirmer $confirmer;
    private UuidGenerator $uuidGenerator;

    public function _before(): void
    {
        $container = DiHelper::container();

        /** @var Handler $handler */
        $this->handler = $container->get(Handler::class);

        /** @var Users $users */
        $this->users = $container->get(Users::class);

        $confirmer = $container->get(Confirmer::class);
        \assert($confirmer instanceof FakeConfirmer);
        $this->confirmer = $confirmer;

        /** @var UuidGenerator $uuidGenerator */
        $this->uuidGenerator = $container->get(UuidGenerator::class);
    }

    public function testSuccessfulConfirmation(FunctionalTester $i): void
    {
        $userId = $this->uuidGenerator->generate();

        $user = User::register(
            id: $userId,
            email: new Email('confirm-' . uniqid() . '@test.local'),
            passwordHash: 'hashed',
            createdAt: new DateTime(),
        );
        $this->users->add($user);

        $this->confirmer->request($userId);

        $token = $this->confirmer->getLastToken();
        $i->assertNotNull($token);

        $result = ($this->handler)(new Envelope(
            message: new Command(token: $token),
            messageId: 'msg-1',
        ));

        $i->assertInstanceOf(Result::class, $result);
        $i->assertSame('Specified email is confirmed', $result->message);

        $confirmed = $this->users->find((string) $userId);
        $i->assertNotNull($confirmed);
        $i->assertSame(UserStatus::Active, $confirmed->getStatus());
    }

    public function testInvalidTokenThrowsException(FunctionalTester $i): void
    {
        $i->expectThrowable(InvalidConfirmationTokenException::class, fn () => ($this->handler)(new Envelope(
            message: new Command(token: 'nonexistent-token-' . uniqid()),
            messageId: 'msg-2',
        )));
    }

    public function testExpiredTokenThrowsException(FunctionalTester $i): void
    {
        $userId = $this->uuidGenerator->generate();

        $user = User::register(
            id: $userId,
            email: new Email('expired-' . uniqid() . '@test.local'),
            passwordHash: 'hashed',
            createdAt: new DateTime(),
        );
        $this->users->add($user);

        $this->confirmer->request($userId);

        $token = $this->confirmer->getLastToken();
        \assert($token !== null);

        $this->confirmer->expireToken($token);

        $i->expectThrowable(ExpiredConfirmationTokenException::class, fn () => ($this->handler)(new Envelope(
            message: new Command(token: $token),
            messageId: 'msg-3',
        )));
    }
}
