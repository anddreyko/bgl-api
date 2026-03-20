<?php

declare(strict_types=1);

namespace Bgl\Tests\Functional\Auth;

use Bgl\Application\Handlers\Auth\ConfirmEmail\Command;
use Bgl\Application\Handlers\Auth\ConfirmEmail\Handler;
use Bgl\Application\Handlers\Auth\ConfirmEmail\Result;
use Bgl\Core\Auth\ExpiredConfirmationTokenException;
use Bgl\Core\Auth\InvalidConfirmationTokenException;
use Bgl\Core\Auth\Verifier;
use Bgl\Core\Identity\UuidGenerator;
use Bgl\Core\Messages\Envelope;
use Bgl\Core\ValueObjects\DateTime;
use Bgl\Core\ValueObjects\Email;
use Bgl\Domain\Profile\User;
use Bgl\Domain\Profile\Users;
use Bgl\Domain\Profile\UserStatus;
use Bgl\Tests\Support\DiHelper;
use Bgl\Tests\Support\Dummy\FakeVerifier;
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
    private FakeVerifier $verifier;
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

        /** @var UuidGenerator $uuidGenerator */
        $this->uuidGenerator = $container->get(UuidGenerator::class);
    }

    public function testSuccessfulConfirmationByToken(FunctionalTester $i): void
    {
        $userId = $this->uuidGenerator->generate();

        $user = User::register(
            id: $userId,
            email: new Email('confirm-' . uniqid() . '@test.local'),
            passwordHash: 'hashed',
            createdAt: new DateTime(),
        );
        $this->users->add($user);

        $credentials = $this->verifier->issue($userId);

        $result = ($this->handler)(new Envelope(
            message: new Command(credential: $credentials->token, type: 'token'),
            messageId: 'msg-1',
        ));

        $i->assertInstanceOf(Result::class, $result);
        $i->assertNotEmpty($result->accessToken);
        $i->assertNotEmpty($result->refreshToken);

        $confirmed = $this->users->find((string) $userId);
        $i->assertNotNull($confirmed);
        $i->assertSame(UserStatus::Active, $confirmed->getStatus());
    }

    public function testSuccessfulConfirmationByCode(FunctionalTester $i): void
    {
        $userId = $this->uuidGenerator->generate();

        $user = User::register(
            id: $userId,
            email: new Email('confirm-code-' . uniqid() . '@test.local'),
            passwordHash: 'hashed',
            createdAt: new DateTime(),
        );
        $this->users->add($user);

        $credentials = $this->verifier->issue($userId);

        $result = ($this->handler)(new Envelope(
            message: new Command(credential: $credentials->code, type: 'code'),
            messageId: 'msg-2',
        ));

        $i->assertInstanceOf(Result::class, $result);
        $i->assertNotEmpty($result->accessToken);
        $i->assertNotEmpty($result->refreshToken);
    }

    public function testInvalidTokenThrowsException(FunctionalTester $i): void
    {
        $i->expectThrowable(InvalidConfirmationTokenException::class, fn () => ($this->handler)(new Envelope(
            message: new Command(credential: 'nonexistent-token-' . uniqid(), type: 'token'),
            messageId: 'msg-3',
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

        $credentials = $this->verifier->issue($userId);
        $this->verifier->expireCredential($credentials->token);

        $i->expectThrowable(ExpiredConfirmationTokenException::class, fn () => ($this->handler)(new Envelope(
            message: new Command(credential: $credentials->token, type: 'token'),
            messageId: 'msg-4',
        )));
    }
}
