<?php

declare(strict_types=1);

namespace Bgl\Tests\Functional\Auth;

use Bgl\Application\Handlers\Auth\ConfirmEmail\Command;
use Bgl\Application\Handlers\Auth\ConfirmEmail\Handler;
use Bgl\Core\Auth\Confirmer;
use Bgl\Core\Auth\ExpiredConfirmationTokenException;
use Bgl\Core\Auth\InvalidConfirmationTokenException;
use Bgl\Core\Identity\UuidGenerator;
use Bgl\Core\Messages\Envelope;
use Bgl\Core\ValueObjects\Email;
use Bgl\Domain\Profile\Entities\User;
use Bgl\Domain\Profile\Entities\Users;
use Bgl\Domain\Profile\Entities\UserStatus;
use Bgl\Infrastructure\Auth\EmailConfirmationToken;
use Bgl\Tests\Support\DiHelper;
use Bgl\Tests\Support\FunctionalTester;
use Codeception\Attribute\Group;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @covers \Bgl\Application\Handlers\Auth\ConfirmEmail\Handler
 */
#[Group('application', 'handler', 'auth', 'confirmation')]
final class ConfirmEmailCest
{
    private EntityManagerInterface $em;
    private Handler $handler;
    private Users $users;
    private Confirmer $confirmer;
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

        /** @var Confirmer $confirmer */
        $this->confirmer = $container->get(Confirmer::class);

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
            createdAt: new \DateTimeImmutable(),
        );
        $this->users->add($user);
        $this->em->flush();

        $this->confirmer->request($userId);
        $this->em->flush();

        // Find the token that was created
        $tokens = $this->em->getRepository(EmailConfirmationToken::class)->findBy(['userId' => $userId]);
        $i->assertCount(1, $tokens);

        /** @var EmailConfirmationToken $token */
        $token = $tokens[0];

        $result = ($this->handler)(new Envelope(
            message: new Command(token: $token->getToken()),
            messageId: 'msg-1',
        ));

        $this->em->flush();

        $i->assertSame('Specified email is confirmed', $result);

        $this->em->clear();
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
            createdAt: new \DateTimeImmutable(),
        );
        $this->users->add($user);
        $this->em->flush();

        $this->confirmer->request($userId);
        $this->em->flush();

        // Expire the token manually
        $tokens = $this->em->getRepository(EmailConfirmationToken::class)->findBy(['userId' => $userId]);

        /** @var EmailConfirmationToken $token */
        $token = $tokens[0];
        $tokenValue = $token->getToken();

        $this->em->createQueryBuilder()
            ->update(EmailConfirmationToken::class, 't')
            ->set('t.expiresAt', ':expired')
            ->where('t.userId = :userId')
            ->setParameter('expired', new \DateTimeImmutable('-1 hour'))
            ->setParameter('userId', $userId)
            ->getQuery()
            ->execute();

        $this->em->clear();

        $i->expectThrowable(ExpiredConfirmationTokenException::class, fn () => ($this->handler)(new Envelope(
            message: new Command(token: $tokenValue),
            messageId: 'msg-3',
        )));
    }
}
