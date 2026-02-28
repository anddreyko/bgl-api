<?php

declare(strict_types=1);

namespace Bgl\Tests\Functional\Plays;

use Bgl\Application\Handlers\Plays\UpdatePlay\Command;
use Bgl\Application\Handlers\Plays\UpdatePlay\Handler;
use Bgl\Application\Handlers\Plays\UpdatePlay\Result;
use Bgl\Core\Exceptions\NotFoundException;
use Bgl\Core\Identity\UuidGenerator;
use Bgl\Core\Messages\Envelope;
use Bgl\Core\ValueObjects\DateTime;
use Bgl\Core\ValueObjects\Uuid;
use Bgl\Domain\Games\Entities\Game;
use Bgl\Domain\Games\Entities\Games;
use Bgl\Domain\Plays\Entities\Play;
use Bgl\Domain\Plays\Entities\Players;
use Bgl\Domain\Plays\Entities\Plays;
use Bgl\Domain\Plays\Entities\PlayStatus;
use Bgl\Domain\Plays\Entities\Visibility;
use Bgl\Tests\Support\DiHelper;
use Bgl\Tests\Support\FunctionalTester;
use Codeception\Attribute\Group;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @covers \Bgl\Application\Handlers\Plays\UpdatePlay\Handler
 */
#[Group('application', 'handler', 'plays', 'update-play')]
final class UpdatePlayCest
{
    private EntityManagerInterface $em;
    private Handler $handler;
    private Plays $plays;
    private Players $players;
    private Games $games;
    private UuidGenerator $uuidGenerator;

    private Uuid $userId;
    private Uuid $gameId;

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

        /** @var Games $games */
        $this->games = $container->get(Games::class);

        /** @var UuidGenerator $uuidGenerator */
        $this->uuidGenerator = $container->get(UuidGenerator::class);

        $this->userId = new Uuid('user-update-' . uniqid());

        $this->gameId = $this->uuidGenerator->generate();
        $this->games->add(Game::create(
            $this->gameId,
            99999,
            'Catan',
            1995,
            new DateTime('now'),
        ));
    }

    public function testSuccessfulUpdate(FunctionalTester $i): void
    {
        $sessionId = $this->uuidGenerator->generate();

        $play = Play::create(
            $sessionId,
            $this->userId,
            'Old name',
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
                name: 'New name',
                gameId: $this->gameId,
                visibility: 'friends',
            ),
            messageId: 'msg-update-1',
        ));

        $i->assertInstanceOf(Result::class, $result);
        $i->assertSame((string) $sessionId, $result->sessionId);

        $this->em->flush();
        $this->em->clear();

        $updated = $this->plays->find((string) $sessionId);
        $i->assertNotNull($updated);
        $i->assertSame('New name', $updated->getName());
        $i->assertSame((string) $this->gameId, $updated->getGameId()?->getValue());
        $i->assertSame(Visibility::Friends, $updated->getVisibility());
        $i->assertSame(PlayStatus::Draft, $updated->getStatus());
    }

    public function testPlayNotFoundThrowsNotFoundException(FunctionalTester $i): void
    {
        $i->expectThrowable(
            new NotFoundException('Play not found'),
            fn () => ($this->handler)(new Envelope(
                message: new Command(
                    sessionId: new Uuid('non-existent-' . uniqid()),
                    userId: $this->userId,
                ),
                messageId: 'msg-update-not-found',
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
            new \DomainException('Access denied'),
            fn () => ($this->handler)(new Envelope(
                message: new Command(
                    sessionId: $sessionId,
                    userId: new Uuid('user-other'),
                ),
                messageId: 'msg-update-denied',
            )),
        );
    }

    public function testUpdateFailsWhenNotDraft(FunctionalTester $i): void
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

        $i->expectThrowable(
            new \DomainException('Play can only be updated in draft status'),
            fn () => ($this->handler)(new Envelope(
                message: new Command(
                    sessionId: $sessionId,
                    userId: $this->userId,
                    name: 'New name',
                ),
                messageId: 'msg-update-not-draft',
            )),
        );
    }

    public function testUpdateFailsWithNonExistentGame(FunctionalTester $i): void
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

        $i->expectThrowable(
            new NotFoundException('Game not found'),
            fn () => ($this->handler)(new Envelope(
                message: new Command(
                    sessionId: $sessionId,
                    userId: $this->userId,
                    gameId: new Uuid('non-existent-game'),
                ),
                messageId: 'msg-update-bad-game',
            )),
        );
    }
}
