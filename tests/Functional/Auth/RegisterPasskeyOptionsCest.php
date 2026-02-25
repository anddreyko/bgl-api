<?php

declare(strict_types=1);

namespace Bgl\Tests\Functional\Auth;

use Bgl\Application\Handlers\Auth\RegisterPasskeyOptions\Command;
use Bgl\Application\Handlers\Auth\RegisterPasskeyOptions\Handler;
use Bgl\Application\Handlers\Auth\RegisterPasskeyOptions\Result;
use Bgl\Core\Auth\AuthenticationException;
use Bgl\Core\Identity\UuidGenerator;
use Bgl\Core\Listing\Filter\All;
use Bgl\Core\Messages\Envelope;
use Bgl\Core\ValueObjects\Email;
use Bgl\Domain\Profile\Entities\PasskeyChallenges;
use Bgl\Domain\Profile\Entities\User;
use Bgl\Domain\Profile\Entities\Users;
use Bgl\Domain\Profile\Entities\UserStatus;
use Bgl\Tests\Support\DiHelper;
use Bgl\Tests\Support\FunctionalTester;
use Codeception\Attribute\Group;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @covers \Bgl\Application\Handlers\Auth\RegisterPasskeyOptions\Handler
 */
#[Group('application', 'handler', 'passkey')]
final class RegisterPasskeyOptionsCest
{
    private EntityManagerInterface $em;
    private Handler $handler;
    private Users $users;
    private PasskeyChallenges $challenges;
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

        /** @var PasskeyChallenges $challenges */
        $this->challenges = $container->get(PasskeyChallenges::class);

        /** @var UuidGenerator $uuidGenerator */
        $this->uuidGenerator = $container->get(UuidGenerator::class);
    }

    public function testRegisterOptionsReturnsJson(FunctionalTester $i): void
    {
        $userId = $this->seedActiveUser();

        /** @var Result $result */
        $result = ($this->handler)(new Envelope(
            message: new Command(userId: (string) $userId),
            messageId: 'test-msg-id',
        ));

        $this->em->flush();

        $i->assertNotEmpty($result->options);
        $i->assertStringContainsString('challenge', $result->options);
    }

    public function testRegisterOptionsUserNotFoundThrows(FunctionalTester $i): void
    {
        $i->expectThrowable(
            AuthenticationException::class,
            fn () => ($this->handler)(new Envelope(
                message: new Command(userId: 'nonexistent-' . uniqid()),
                messageId: 'test-msg-id',
            )),
        );
    }

    public function testRegisterOptionsSavesChallenge(FunctionalTester $i): void
    {
        $userId = $this->seedActiveUser();

        ($this->handler)(new Envelope(
            message: new Command(userId: (string) $userId),
            messageId: 'test-msg-id',
        ));

        $this->em->flush();

        $results = iterator_to_array($this->challenges->search(All::Filter));
        $i->assertNotEmpty($results);
    }

    private function seedActiveUser(): \Bgl\Core\ValueObjects\Uuid
    {
        $userId = $this->uuidGenerator->generate();
        $user = new User(
            id: $userId,
            email: new Email('passkey-' . uniqid() . '@test.local'),
            passwordHash: 'hashed',
            createdAt: new \DateTimeImmutable(),
            status: UserStatus::Active,
            name: 'Test User',
        );
        $this->users->add($user);
        $this->em->flush();
        $this->em->clear();

        return $userId;
    }
}
