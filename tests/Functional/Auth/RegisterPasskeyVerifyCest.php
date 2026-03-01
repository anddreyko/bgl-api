<?php

declare(strict_types=1);

namespace Bgl\Tests\Functional\Auth;

use Bgl\Application\Handlers\Auth\RegisterPasskeyOptions\Command as OptionsCommand;
use Bgl\Application\Handlers\Auth\RegisterPasskeyOptions\Handler as OptionsHandler;
use Bgl\Application\Handlers\Auth\RegisterPasskeyVerify\Command;
use Bgl\Application\Handlers\Auth\RegisterPasskeyVerify\Handler;
use Bgl\Application\Handlers\Auth\RegisterPasskeyVerify\Result;
use Bgl\Core\Auth\AuthenticationException;
use Bgl\Core\Identity\UuidGenerator;
use Bgl\Core\Messages\Envelope;
use Bgl\Core\ValueObjects\DateTime;
use Bgl\Core\ValueObjects\Email;
use Bgl\Domain\Profile\Passkey\Passkeys;
use Bgl\Domain\Profile\User;
use Bgl\Domain\Profile\Users;
use Bgl\Domain\Profile\UserStatus;
use Bgl\Tests\Support\DiHelper;
use Bgl\Tests\Support\FunctionalTester;
use Codeception\Attribute\Group;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @covers \Bgl\Application\Handlers\Auth\RegisterPasskeyVerify\Handler
 */
#[Group('application', 'handler', 'passkey')]
final class RegisterPasskeyVerifyCest
{
    private EntityManagerInterface $em;
    private Handler $handler;
    private OptionsHandler $optionsHandler;
    private Users $users;
    private Passkeys $passkeys;
    private UuidGenerator $uuidGenerator;

    public function _before(): void
    {
        $container = DiHelper::container();

        /** @var EntityManagerInterface $em */
        $this->em = $container->get(EntityManagerInterface::class);

        /** @var Handler $handler */
        $this->handler = $container->get(Handler::class);

        /** @var OptionsHandler $optionsHandler */
        $this->optionsHandler = $container->get(OptionsHandler::class);

        /** @var Users $users */
        $this->users = $container->get(Users::class);

        /** @var Passkeys $passkeys */
        $this->passkeys = $container->get(Passkeys::class);

        /** @var UuidGenerator $uuidGenerator */
        $this->uuidGenerator = $container->get(UuidGenerator::class);
    }

    public function testSuccessfulRegistrationSavesPasskey(FunctionalTester $i): void
    {
        $userId = $this->seedActiveUser();

        // First, request options to create a challenge
        ($this->optionsHandler)(new Envelope(
            message: new OptionsCommand(userId: (string) $userId),
            messageId: 'options-msg',
        ));
        $this->em->flush();

        $result = ($this->handler)(new Envelope(
            message: new Command(userId: (string) $userId, response: 'fake-webauthn-response'),
            messageId: 'verify-msg',
        ));
        $this->em->flush();

        $i->assertInstanceOf(Result::class, $result);
        $i->assertSame('ok', $result->message);
    }

    public function testNoChallengeFoundThrows(FunctionalTester $i): void
    {
        $userId = $this->seedActiveUser();

        $i->expectThrowable(
            AuthenticationException::class,
            fn () => ($this->handler)(new Envelope(
                message: new Command(userId: (string) $userId, response: 'fake-webauthn-response'),
                messageId: 'verify-msg',
            )),
        );
    }

    public function testEmptyUserIdThrows(FunctionalTester $i): void
    {
        $i->expectThrowable(
            AuthenticationException::class,
            fn () => ($this->handler)(new Envelope(
                message: new Command(userId: '', response: 'fake-webauthn-response'),
                messageId: 'verify-msg',
            )),
        );
    }

    private function seedActiveUser(): \Bgl\Core\ValueObjects\Uuid
    {
        $userId = $this->uuidGenerator->generate();
        $user = new User(
            id: $userId,
            email: new Email('passkey-verify-' . uniqid() . '@test.local'),
            passwordHash: 'hashed',
            createdAt: new DateTime(),
            status: UserStatus::Active,
            name: 'Test User',
        );
        $this->users->add($user);
        $this->em->flush();
        $this->em->clear();

        return $userId;
    }
}
