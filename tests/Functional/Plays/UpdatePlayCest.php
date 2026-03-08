<?php

declare(strict_types=1);

namespace Bgl\Tests\Functional\Plays;

use Bgl\Application\Handlers\Plays\UpdatePlay\Command;
use Bgl\Application\Handlers\Plays\UpdatePlay\Handler;
use Bgl\Application\Handlers\Plays\UpdatePlay\Result;
use Bgl\Core\Exceptions\NotFoundException;
use Bgl\Core\Identity\UuidGenerator;
use Bgl\Domain\Plays\DuplicatePlayerException;
use Bgl\Domain\Plays\PlayAccessDeniedException;
use Bgl\Domain\Plays\PlayDeletedException;
use Bgl\Core\Messages\Envelope;
use Bgl\Core\ValueObjects\DateTime;
use Bgl\Core\ValueObjects\Uuid;
use Bgl\Domain\Games\Game;
use Bgl\Domain\Games\Games;
use Bgl\Domain\Mates\Mate;
use Bgl\Domain\Mates\Mates;
use Bgl\Domain\Plays\Play;
use Bgl\Domain\Plays\Player\Player;
use Bgl\Domain\Plays\Player\Players;
use Bgl\Domain\Plays\Plays;
use Bgl\Domain\Plays\PlayStatus;
use Bgl\Domain\Plays\Visibility;
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
    private Mates $mates;
    private Games $games;
    private UuidGenerator $uuidGenerator;

    private Uuid $userId;
    private Uuid $gameId;
    private Uuid $mate1Id;
    private Uuid $mate2Id;
    private Uuid $mate3Id;

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

        /** @var Mates $mates */
        $this->mates = $container->get(Mates::class);

        /** @var Games $games */
        $this->games = $container->get(Games::class);

        /** @var UuidGenerator $uuidGenerator */
        $this->uuidGenerator = $container->get(UuidGenerator::class);

        $this->userId = $this->uuidGenerator->generate();

        $this->mate1Id = $this->uuidGenerator->generate();
        $this->mate2Id = $this->uuidGenerator->generate();
        $this->mate3Id = $this->uuidGenerator->generate();

        $this->mates->add(Mate::create($this->mate1Id, $this->userId, 'Alice', null, new DateTime()));
        $this->mates->add(Mate::create($this->mate2Id, $this->userId, 'Bob', null, new DateTime()));
        $this->mates->add(Mate::create($this->mate3Id, $this->userId, 'Charlie', null, new DateTime()));

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
                visibility: 'participants',
            ),
            messageId: 'msg-update-1',
        ));

        $i->assertInstanceOf(Result::class, $result);
        $i->assertSame((string) $sessionId, $result->id);
        $i->assertIsArray($result->author);
        $i->assertSame((string) $this->userId, $result->author['id']);

        $this->em->flush();
        $this->em->clear();

        $updated = $this->plays->find((string) $sessionId);
        $i->assertNotNull($updated);
        $i->assertSame('New name', $updated->getName());
        $i->assertSame((string) $this->gameId, $updated->getGameId()?->getValue());
        $i->assertSame(Visibility::Participants, $updated->getVisibility());
        $i->assertSame(PlayStatus::Draft, $updated->getStatus());
    }

    public function testUpdateWithNotes(FunctionalTester $i): void
    {
        $sessionId = $this->uuidGenerator->generate();

        $play = Play::create(
            $sessionId,
            $this->userId,
            'Game night',
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
                name: 'Game night',
                notes: 'Great session, everyone had fun!',
            ),
            messageId: 'msg-update-notes',
        ));

        $i->assertInstanceOf(Result::class, $result);
        $i->assertSame('Great session, everyone had fun!', $result->notes);

        $this->em->flush();
        $this->em->clear();

        $updated = $this->plays->find((string) $sessionId);
        $i->assertNotNull($updated);
        $i->assertSame('Great session, everyone had fun!', $updated->getNotes());
    }

    public function testUpdateClearsNotes(FunctionalTester $i): void
    {
        $sessionId = $this->uuidGenerator->generate();

        $play = Play::create(
            $sessionId,
            $this->userId,
            'Game night',
            new DateTime('2024-06-15 20:00:00'),
            $this->players,
            notes: 'Old notes',
        );
        $this->plays->add($play);
        $this->em->flush();
        $this->em->clear();

        $result = ($this->handler)(new Envelope(
            message: new Command(
                sessionId: $sessionId,
                userId: $this->userId,
                name: 'Game night',
            ),
            messageId: 'msg-update-clear-notes',
        ));

        $i->assertInstanceOf(Result::class, $result);
        $i->assertNull($result->notes);

        $this->em->flush();
        $this->em->clear();

        $updated = $this->plays->find((string) $sessionId);
        $i->assertNotNull($updated);
        $i->assertNull($updated->getNotes());
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
                messageId: 'msg-update-not-found',
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
                messageId: 'msg-update-denied',
            )),
        );
    }

    public function testUpdateWorksWhenPublished(FunctionalTester $i): void
    {
        $sessionId = $this->uuidGenerator->generate();

        $play = Play::create(
            $sessionId,
            $this->userId,
            null,
            new DateTime('2024-06-15 20:00:00'),
            $this->players,
        );
        $play->update(null, null, Visibility::Private, PlayStatus::Published);
        $this->plays->add($play);
        $this->em->flush();
        $this->em->clear();

        $result = ($this->handler)(new Envelope(
            message: new Command(
                sessionId: $sessionId,
                userId: $this->userId,
                name: 'Updated name',
                gameId: $this->gameId,
                visibility: 'public',
            ),
            messageId: 'msg-update-published',
        ));

        $i->assertInstanceOf(Result::class, $result);

        $this->em->flush();
        $this->em->clear();

        $updated = $this->plays->find((string) $sessionId);
        $i->assertNotNull($updated);
        $i->assertSame('Updated name', $updated->getName());
        $i->assertSame((string) $this->gameId, $updated->getGameId()?->getValue());
        $i->assertSame(Visibility::Public, $updated->getVisibility());
        $i->assertSame(PlayStatus::Published, $updated->getStatus());
    }

    public function testStatusChangeDraftToPublished(FunctionalTester $i): void
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

        $result = ($this->handler)(new Envelope(
            message: new Command(
                sessionId: $sessionId,
                userId: $this->userId,
                status: 'published',
            ),
            messageId: 'msg-update-publish',
        ));

        $i->assertInstanceOf(Result::class, $result);

        $this->em->flush();
        $this->em->clear();

        $updated = $this->plays->find((string) $sessionId);
        $i->assertNotNull($updated);
        $i->assertSame(PlayStatus::Published, $updated->getStatus());
    }

    public function testStatusChangePublishedToDraft(FunctionalTester $i): void
    {
        $sessionId = $this->uuidGenerator->generate();

        $play = Play::create(
            $sessionId,
            $this->userId,
            null,
            new DateTime('2024-06-15 20:00:00'),
            $this->players,
        );
        $play->update(null, null, Visibility::Private, PlayStatus::Published);
        $this->plays->add($play);
        $this->em->flush();
        $this->em->clear();

        $result = ($this->handler)(new Envelope(
            message: new Command(
                sessionId: $sessionId,
                userId: $this->userId,
                status: 'draft',
            ),
            messageId: 'msg-update-unpublish',
        ));

        $i->assertInstanceOf(Result::class, $result);

        $this->em->flush();
        $this->em->clear();

        $updated = $this->plays->find((string) $sessionId);
        $i->assertNotNull($updated);
        $i->assertSame(PlayStatus::Draft, $updated->getStatus());
    }

    public function testStatusChangeToDeletedThrows(FunctionalTester $i): void
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
            PlayDeletedException::class,
            fn () => ($this->handler)(new Envelope(
                message: new Command(
                    sessionId: $sessionId,
                    userId: $this->userId,
                    status: 'deleted',
                ),
                messageId: 'msg-update-delete-status',
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
                    gameId: new Uuid('00000000-0000-4000-8000-000000000001'),
                ),
                messageId: 'msg-update-bad-game',
            )),
        );
    }

    public function testUpdateWithPlayers(FunctionalTester $i): void
    {
        $sessionId = $this->uuidGenerator->generate();

        $play = Play::create(
            $sessionId,
            $this->userId,
            'Game night',
            new DateTime('2024-06-15 20:00:00'),
            $this->players,
        );
        $play->addPlayer(Player::create(
            $this->uuidGenerator->generate(),
            $play,
            $this->mate1Id,
            5,
            false,
            'red',
        ));
        $this->plays->add($play);
        $this->em->flush();
        $this->em->clear();

        $result = ($this->handler)(new Envelope(
            message: new Command(
                sessionId: $sessionId,
                userId: $this->userId,
                name: 'Game night',
                players: [
                    ['mate_id' => (string) $this->mate2Id, 'score' => 10, 'is_winner' => true, 'color' => 'blue'],
                    ['mate_id' => (string) $this->mate3Id, 'score' => 3, 'is_winner' => false, 'color' => 'green'],
                ],
            ),
            messageId: 'msg-update-players',
        ));

        $i->assertInstanceOf(Result::class, $result);
        $i->assertCount(2, $result->players);

        $this->em->flush();
        $this->em->clear();

        $updated = $this->plays->find((string) $sessionId);
        $i->assertNotNull($updated);
        $i->assertSame(2, $updated->getPlayers()->count());

        $mateIds = [];
        /** @var Player $player */
        foreach ($updated->getPlayers() as $player) {
            $mateIds[] = (string) $player->getMateId();
        }
        $i->assertContains((string) $this->mate2Id, $mateIds);
        $i->assertContains((string) $this->mate3Id, $mateIds);
        $i->assertNotContains((string) $this->mate1Id, $mateIds);
    }

    public function testUpdateWithoutPlayersKeepsExisting(FunctionalTester $i): void
    {
        $sessionId = $this->uuidGenerator->generate();

        $play = Play::create(
            $sessionId,
            $this->userId,
            'Game night',
            new DateTime('2024-06-15 20:00:00'),
            $this->players,
        );
        $play->addPlayer(Player::create(
            $this->uuidGenerator->generate(),
            $play,
            $this->mate1Id,
            5,
            false,
            'red',
        ));
        $this->plays->add($play);
        $this->em->flush();
        $this->em->clear();

        $result = ($this->handler)(new Envelope(
            message: new Command(
                sessionId: $sessionId,
                userId: $this->userId,
                name: 'Updated name',
            ),
            messageId: 'msg-update-no-players',
        ));

        $i->assertInstanceOf(Result::class, $result);
        $i->assertCount(1, $result->players);

        $this->em->flush();
        $this->em->clear();

        $updated = $this->plays->find((string) $sessionId);
        $i->assertNotNull($updated);
        $i->assertSame(1, $updated->getPlayers()->count());
    }

    public function testUpdateWithDuplicatePlayerThrows(FunctionalTester $i): void
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
            DuplicatePlayerException::class,
            fn () => ($this->handler)(new Envelope(
                message: new Command(
                    sessionId: $sessionId,
                    userId: $this->userId,
                    players: [
                        ['mate_id' => (string) $this->mate1Id],
                        ['mate_id' => (string) $this->mate1Id],
                    ],
                ),
                messageId: 'msg-update-dup-player',
            )),
        );
    }

    public function testUpdateWithNonExistentMateThrows(FunctionalTester $i): void
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
            NotFoundException::class,
            fn () => ($this->handler)(new Envelope(
                message: new Command(
                    sessionId: $sessionId,
                    userId: $this->userId,
                    players: [
                        ['mate_id' => 'non-existent-mate-id'],
                    ],
                ),
                messageId: 'msg-update-bad-mate',
            )),
        );
    }
}
