<?php

declare(strict_types=1);

namespace Bgl\Tests\Functional\Plays;

use Bgl\Application\Handlers\Plays\FinalizePlay\Command;
use Bgl\Application\Handlers\Plays\FinalizePlay\Handler;
use Bgl\Application\Handlers\Plays\FinalizePlay\Result;
use Bgl\Core\Exceptions\NotFoundException;
use Bgl\Core\Identity\UuidGenerator;
use Bgl\Domain\Plays\PlayAccessDeniedException;
use Bgl\Core\Messages\Envelope;
use Bgl\Core\ValueObjects\DateTime;
use Bgl\Core\ValueObjects\Uuid;
use Bgl\Domain\Plays\Play;
use Bgl\Domain\Plays\Player\Players;
use Bgl\Domain\Plays\Plays;
use Bgl\Domain\Plays\PlayStatus;
use Bgl\Tests\Support\DiHelper;
use Bgl\Tests\Support\FunctionalTester;
use Codeception\Attribute\Group;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @covers \Bgl\Application\Handlers\Plays\FinalizePlay\Handler
 */
#[Group('application', 'handler', 'plays', 'close-session')]
final class CloseSessionCest
{
    private EntityManagerInterface $em;
    private Handler $handler;
    private Plays $plays;
    private Players $players;
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

        /** @var Players $players */
        $this->players = $container->get(Players::class);

        /** @var UuidGenerator $uuidGenerator */
        $this->uuidGenerator = $container->get(UuidGenerator::class);
    }

    public function testSuccessfulClose(FunctionalTester $i): void
    {
        $sessionId = $this->uuidGenerator->generate();
        $userId = new Uuid('user-123');

        $play = Play::create(
            $sessionId,
            $userId,
            'Game night',
            new DateTime('2024-06-15 20:00:00'),
            $this->players,
        );
        $this->plays->add($play);
        $this->em->flush();
        $this->em->clear();

        $result = ($this->handler)(new Envelope(
            message: new Command(sessionId: $sessionId, userId: $userId),
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

    public function testPlayNotFoundThrowsNotFoundException(FunctionalTester $i): void
    {
        $i->expectThrowable(
            new NotFoundException('Play not found'),
            fn () => ($this->handler)(new Envelope(
                message: new Command(sessionId: new Uuid('non-existent-' . uniqid()), userId: new Uuid('user-123')),
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
            new DateTime('2024-06-15 20:00:00'),
            $this->players,
        );
        $this->plays->add($play);
        $this->em->flush();
        $this->em->clear();

        $i->expectThrowable(
            new PlayAccessDeniedException(),
            fn () => ($this->handler)(new Envelope(
                message: new Command(sessionId: $sessionId, userId: new Uuid('user-other')),
                messageId: 'msg-3',
            )),
        );
    }
}
