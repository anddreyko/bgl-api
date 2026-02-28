<?php

declare(strict_types=1);

namespace Bgl\Tests\Functional\Plays;

use Bgl\Application\Handlers\Plays\CloseSession\Command;
use Bgl\Application\Handlers\Plays\CloseSession\Handler;
use Bgl\Application\Handlers\Plays\CloseSession\Result;
use Bgl\Core\Identity\UuidGenerator;
use Bgl\Core\Messages\Envelope;
use Bgl\Core\ValueObjects\Uuid;
use Bgl\Domain\Plays\Entities\Play;
use Bgl\Domain\Plays\Entities\Plays;
use Bgl\Domain\Plays\Entities\PlayStatus;
use Bgl\Tests\Support\DiHelper;
use Bgl\Tests\Support\FunctionalTester;
use Codeception\Attribute\Group;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @covers \Bgl\Application\Handlers\Plays\CloseSession\Handler
 */
#[Group('application', 'handler', 'plays', 'close-session')]
final class CloseSessionCest
{
    private EntityManagerInterface $em;
    private Handler $handler;
    private Plays $plays;
    private UuidGenerator $uuidGenerator;

    public function _before(): void
    {
        $container = DiHelper::container();

        /** @var EntityManagerInterface $em */
        $this->em = $container->get(EntityManagerInterface::class);

        /** @var Handler $handler */
        $this->handler = $container->get(Handler::class);

        /** @var Plays $plays */
        $this->plays = $container->get(Plays::class);

        /** @var UuidGenerator $uuidGenerator */
        $this->uuidGenerator = $container->get(UuidGenerator::class);
    }

    public function testSuccessfulClose(FunctionalTester $i): void
    {
        $sessionId = $this->uuidGenerator->generate();
        $userId = 'user-123';

        $play = Play::create(
            $sessionId,
            new Uuid($userId),
            'Game night',
            new \DateTimeImmutable('2024-06-15 20:00:00'),
        );
        $this->plays->add($play);
        $this->em->flush();
        $this->em->clear();

        $result = ($this->handler)(new Envelope(
            message: new Command(sessionId: (string) $sessionId, userId: $userId),
            messageId: 'msg-1',
        ));

        $this->em->flush();

        $i->assertInstanceOf(Result::class, $result);
        $i->assertSame((string) $sessionId, $result->sessionId);
        $i->assertNotNull($result->finishedAt);

        $this->em->clear();
        $closedPlay = $this->plays->find((string) $sessionId);
        $i->assertNotNull($closedPlay);
        $i->assertSame(PlayStatus::Published, $closedPlay->getStatus());
    }

    public function testPlayNotFoundThrowsDomainException(FunctionalTester $i): void
    {
        $i->expectThrowable(
            new \DomainException('Play not found'),
            fn () => ($this->handler)(new Envelope(
                message: new Command(sessionId: 'non-existent-' . uniqid(), userId: 'user-123'),
                messageId: 'msg-2',
            )),
        );
    }

    public function testAccessDeniedThrowsDomainException(FunctionalTester $i): void
    {
        $sessionId = $this->uuidGenerator->generate();

        $play = Play::create(
            $sessionId,
            new Uuid('user-owner'),
            null,
            new \DateTimeImmutable('2024-06-15 20:00:00'),
        );
        $this->plays->add($play);
        $this->em->flush();
        $this->em->clear();

        $i->expectThrowable(
            new \DomainException('Access denied'),
            fn () => ($this->handler)(new Envelope(
                message: new Command(sessionId: (string) $sessionId, userId: 'user-other'),
                messageId: 'msg-3',
            )),
        );
    }
}
