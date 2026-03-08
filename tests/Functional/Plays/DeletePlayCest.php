<?php

declare(strict_types=1);

namespace Bgl\Tests\Functional\Plays;

use Bgl\Application\Handlers\Plays\DeletePlay\Command;
use Bgl\Application\Handlers\Plays\DeletePlay\Handler;
use Bgl\Core\Exceptions\NotFoundException;
use Bgl\Core\Identity\UuidGenerator;
use Bgl\Core\Messages\Envelope;
use Bgl\Core\ValueObjects\DateTime;
use Bgl\Core\ValueObjects\Uuid;
use Bgl\Domain\Plays\Play;
use Bgl\Domain\Plays\PlayAccessDeniedException;
use Bgl\Domain\Plays\Player\Players;
use Bgl\Domain\Plays\Plays;
use Bgl\Domain\Plays\PlayStatus;
use Bgl\Tests\Support\DiHelper;
use Bgl\Tests\Support\FunctionalTester;
use Codeception\Attribute\Group;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @covers \Bgl\Application\Handlers\Plays\DeletePlay\Handler
 */
#[Group('application', 'handler', 'plays', 'delete-play')]
final class DeletePlayCest
{
    private EntityManagerInterface $em;
    private Handler $handler;
    private Plays $plays;
    private Players $players;
    private UuidGenerator $uuidGenerator;

    private Uuid $userId;

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

        $this->userId = $this->uuidGenerator->generate();
    }

    public function testSuccessfulDelete(FunctionalTester $i): void
    {
        $sessionId = $this->uuidGenerator->generate();

        $play = Play::create(
            $sessionId,
            $this->userId,
            'Game to delete',
            new DateTime('2024-06-15 20:00:00'),
            $this->players,
        );
        $this->plays->add($play);
        $this->em->flush();
        $this->em->clear();

        $result = ($this->handler)(new Envelope(
            message: new Command(
                sessionId: $sessionId,
                userId: $this->userId,
            ),
            messageId: 'msg-delete-1',
        ));

        $i->assertNull($result);

        $this->em->flush();
        $this->em->clear();

        $deleted = $this->plays->find((string) $sessionId);
        $i->assertNotNull($deleted);
        $i->assertSame(PlayStatus::Deleted, $deleted->getStatus());
    }

    public function testDeletePublishedPlay(FunctionalTester $i): void
    {
        $sessionId = $this->uuidGenerator->generate();

        $play = Play::create(
            $sessionId,
            $this->userId,
            null,
            new DateTime('2024-06-15 20:00:00'),
            $this->players,
        );
        $play->finalize(new DateTime('2024-06-15 23:00:00'));
        $this->plays->add($play);
        $this->em->flush();
        $this->em->clear();

        $result = ($this->handler)(new Envelope(
            message: new Command(
                sessionId: $sessionId,
                userId: $this->userId,
            ),
            messageId: 'msg-delete-published',
        ));

        $i->assertNull($result);

        $this->em->flush();
        $this->em->clear();

        $deleted = $this->plays->find((string) $sessionId);
        $i->assertNotNull($deleted);
        $i->assertSame(PlayStatus::Deleted, $deleted->getStatus());
    }

    public function testPlayNotFoundThrowsNotFoundException(FunctionalTester $i): void
    {
        $i->expectThrowable(
            new NotFoundException('Play not found'),
            fn () => ($this->handler)(new Envelope(
                message: new Command(
                    sessionId: $this->uuidGenerator->generate(),
                    userId: $this->userId,
                ),
                messageId: 'msg-delete-not-found',
            )),
        );
    }

    public function testAccessDeniedThrowsDomainException(FunctionalTester $i): void
    {
        $sessionId = $this->uuidGenerator->generate();

        $play = Play::create(
            $sessionId,
            new Uuid('eeeeeeee-eeee-4eee-8eee-eeeeeeeeeeee'),
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
                message: new Command(
                    sessionId: $sessionId,
                    userId: new Uuid('ffffffff-ffff-4fff-8fff-ffffffffffff'),
                ),
                messageId: 'msg-delete-denied',
            )),
        );
    }

    public function testAlreadyDeletedThrowsNotFoundException(FunctionalTester $i): void
    {
        $sessionId = $this->uuidGenerator->generate();

        $play = Play::create(
            $sessionId,
            $this->userId,
            null,
            new DateTime('2024-06-15 20:00:00'),
            $this->players,
        );
        $this->plays->add($play);
        $this->em->flush();
        $this->em->clear();

        ($this->handler)(new Envelope(
            message: new Command(
                sessionId: $sessionId,
                userId: $this->userId,
            ),
            messageId: 'msg-delete-first',
        ));

        $this->em->flush();
        $this->em->clear();

        $i->expectThrowable(
            new NotFoundException('Play not found'),
            fn () => ($this->handler)(new Envelope(
                message: new Command(
                    sessionId: $sessionId,
                    userId: $this->userId,
                ),
                messageId: 'msg-delete-again',
            )),
        );
    }
}
