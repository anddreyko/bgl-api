<?php

declare(strict_types=1);

namespace Bgl\Tests\Functional\Plays;

use Bgl\Application\Handlers\Plays\RestorePlay\Command;
use Bgl\Application\Handlers\Plays\RestorePlay\Handler;
use Bgl\Application\Handlers\Plays\RestorePlay\Result;
use Bgl\Core\Exceptions\NotFoundException;
use Bgl\Core\Identity\UuidGenerator;
use Bgl\Core\Messages\Envelope;
use Bgl\Core\ValueObjects\DateTime;
use Bgl\Core\ValueObjects\Uuid;
use Bgl\Domain\Plays\Play;
use Bgl\Domain\Plays\PlayAccessDeniedException;
use Bgl\Domain\Plays\PlayLifecycle;
use Bgl\Domain\Plays\PlayNotDeletedException;
use Bgl\Domain\Plays\Player\Players;
use Bgl\Domain\Plays\Plays;
use Bgl\Tests\Support\DiHelper;
use Bgl\Tests\Support\FunctionalTester;
use Codeception\Attribute\Group;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @covers \Bgl\Application\Handlers\Plays\RestorePlay\Handler
 */
#[Group('application', 'handler', 'plays', 'restore-play')]
final class RestorePlayCest
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

    public function testSuccessfulRestore(FunctionalTester $i): void
    {
        $sessionId = $this->uuidGenerator->generate();

        $play = Play::create(
            $sessionId,
            $this->userId,
            'Deleted game',
            new DateTime('2024-06-15 20:00:00'),
            $this->players,
        );
        $play->finalize(new DateTime('2024-06-15 22:00:00'));
        $play->delete();
        $this->plays->add($play);
        $this->em->flush();
        $this->em->clear();

        $result = ($this->handler)(new Envelope(
            message: new Command(
                sessionId: $sessionId,
                userId: $this->userId,
            ),
            messageId: 'msg-restore-1',
        ));

        $i->assertInstanceOf(Result::class, $result);
        $i->assertSame((string) $sessionId, $result->id);

        $this->em->flush();
        $this->em->clear();

        $restored = $this->plays->find((string) $sessionId);
        $i->assertNotNull($restored);
        $i->assertSame(PlayLifecycle::Finished, $restored->getLifecycle());
    }

    public function testRestoreNonDeletedThrows(FunctionalTester $i): void
    {
        $sessionId = $this->uuidGenerator->generate();

        $play = Play::create(
            $sessionId,
            $this->userId,
            'Active game',
            new DateTime('2024-06-15 20:00:00'),
            $this->players,
        );
        $this->plays->add($play);
        $this->em->flush();
        $this->em->clear();

        $i->expectThrowable(
            PlayNotDeletedException::class,
            fn () => ($this->handler)(new Envelope(
                message: new Command(
                    sessionId: $sessionId,
                    userId: $this->userId,
                ),
                messageId: 'msg-restore-not-deleted',
            )),
        );
    }

    public function testRestoreOtherUserSessionThrows(FunctionalTester $i): void
    {
        $sessionId = $this->uuidGenerator->generate();

        $play = Play::create(
            $sessionId,
            new Uuid('eeeeeeee-eeee-4eee-8eee-eeeeeeeeeeee'),
            'Other user game',
            new DateTime('2024-06-15 20:00:00'),
            $this->players,
        );
        $play->finalize(new DateTime('2024-06-15 22:00:00'));
        $play->delete();
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
                messageId: 'msg-restore-denied',
            )),
        );
    }

    public function testRestoreNonExistentThrowsNotFound(FunctionalTester $i): void
    {
        $i->expectThrowable(
            new NotFoundException('Play not found'),
            fn () => ($this->handler)(new Envelope(
                message: new Command(
                    sessionId: $this->uuidGenerator->generate(),
                    userId: $this->userId,
                ),
                messageId: 'msg-restore-not-found',
            )),
        );
    }
}
