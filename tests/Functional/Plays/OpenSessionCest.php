<?php

declare(strict_types=1);

namespace Bgl\Tests\Functional\Plays;

use Bgl\Application\Handlers\Plays\CreatePlay\Command;
use Bgl\Application\Handlers\Plays\CreatePlay\Handler;
use Bgl\Application\Handlers\Plays\CreatePlay\Result;
use Bgl\Core\Exceptions\NotFoundException;
use Bgl\Core\Identity\UuidGenerator;
use Bgl\Domain\Plays\DuplicatePlayerException;
use Bgl\Domain\Plays\FinishedAtBeforeStartedAtException;
use Bgl\Domain\Plays\MateNotOwnedByUserException;
use Bgl\Core\Messages\Envelope;
use Bgl\Core\ValueObjects\DateTime;
use Bgl\Core\ValueObjects\Uuid;
use Bgl\Domain\Games\Game;
use Bgl\Domain\Games\Games;
use Bgl\Domain\Mates\Mate;
use Bgl\Domain\Mates\Mates;
use Bgl\Domain\Plays\Plays;
use Bgl\Domain\Plays\PlayStatus;
use Bgl\Tests\Support\DiHelper;
use Bgl\Tests\Support\FunctionalTester;
use Codeception\Attribute\Group;

/**
 * @covers \Bgl\Application\Handlers\Plays\CreatePlay\Handler
 */
#[Group('application', 'handler', 'plays', 'open-session')]
final class OpenSessionCest
{
    private Handler $handler;
    private Plays $plays;
    private Mates $mates;
    private Games $games;
    private UuidGenerator $uuidGenerator;

    private Uuid $userId;
    private Uuid $mate1Id;
    private Uuid $mate2Id;
    private Uuid $gameId;

    public function _before(): void
    {
        $container = DiHelper::container();

        /** @var Handler $handler */
        $this->handler = $container->get(Handler::class);

        /** @var Plays $plays */
        $this->plays = $container->get(Plays::class);

        /** @var Mates $mates */
        $this->mates = $container->get(Mates::class);

        /** @var Games $games */
        $this->games = $container->get(Games::class);

        /** @var UuidGenerator $uuidGenerator */
        $this->uuidGenerator = $container->get(UuidGenerator::class);

        $this->userId = $this->uuidGenerator->generate();

        $this->mate1Id = $this->uuidGenerator->generate();
        $this->mate2Id = $this->uuidGenerator->generate();

        $this->mates->add(Mate::create(
            $this->mate1Id,
            $this->userId,
            'Alice',
            null,
            new DateTime(),
        ));
        $this->mates->add(Mate::create(
            $this->mate2Id,
            $this->userId,
            'Bob',
            null,
            new DateTime(),
        ));

        $this->gameId = $this->uuidGenerator->generate();
        $this->games->add(Game::create(
            $this->gameId,
            12345,
            'Catan',
            1995,
            new DateTime(),
        ));
    }

    public function testOpenSessionWithAllFields(FunctionalTester $i): void
    {
        $result = ($this->handler)(new Envelope(
            message: new Command(
                userId: $this->userId,
                name: 'Friday game night',
                players: [
                    ['mate_id' => (string) $this->mate1Id, 'score' => 10, 'is_winner' => true, 'color' => 'red'],
                    ['mate_id' => (string) $this->mate2Id, 'score' => 5, 'is_winner' => false, 'color' => 'blue'],
                ],
                gameId: $this->gameId,
                startedAt: new DateTime('2024-06-15 20:00:00'),
                visibility: 'participants',
            ),
            messageId: 'msg-open-1',
        ));

        $i->assertInstanceOf(Result::class, $result);
        $i->assertNotEmpty($result->id);
        $i->assertIsArray($result->author);
        $i->assertSame((string) $this->userId, $result->author['id']);
        $i->assertNotNull($this->plays->find($result->id));
    }

    public function testOpenSessionWithNotes(FunctionalTester $i): void
    {
        $result = ($this->handler)(new Envelope(
            message: new Command(
                userId: $this->userId,
                name: 'Game with notes',
                notes: 'We played on the terrace, great weather!',
            ),
            messageId: 'msg-open-notes',
        ));

        $i->assertInstanceOf(Result::class, $result);
        $i->assertSame('We played on the terrace, great weather!', $result->notes);

        $play = $this->plays->find($result->id);
        $i->assertNotNull($play);
        $i->assertSame('We played on the terrace, great weather!', $play->getNotes());
    }

    public function testOpenSessionWithoutNotesReturnsNull(FunctionalTester $i): void
    {
        $result = ($this->handler)(new Envelope(
            message: new Command(
                userId: $this->userId,
            ),
            messageId: 'msg-open-no-notes',
        ));

        $i->assertInstanceOf(Result::class, $result);
        $i->assertNull($result->notes);
    }

    public function testOpenSessionWithMinimalFields(FunctionalTester $i): void
    {
        $result = ($this->handler)(new Envelope(
            message: new Command(
                userId: $this->userId,
            ),
            messageId: 'msg-open-minimal',
        ));

        $i->assertInstanceOf(Result::class, $result);
        $i->assertNotEmpty($result->id);

        $play = $this->plays->find($result->id);
        $i->assertNotNull($play);
        $i->assertSame(PlayStatus::Draft, $play->getStatus());
    }

    public function testOpenSessionWithPlayers(FunctionalTester $i): void
    {
        $result = ($this->handler)(new Envelope(
            message: new Command(
                userId: $this->userId,
                players: [
                    ['mate_id' => (string) $this->mate1Id],
                ],
            ),
            messageId: 'msg-open-with-players',
        ));

        $i->assertInstanceOf(Result::class, $result);
        $i->assertNotEmpty($result->id);

        $play = $this->plays->find($result->id);
        $i->assertNotNull($play);
        $i->assertSame(PlayStatus::Draft, $play->getStatus());
    }

    public function testOpenSessionWithFinishedAt(FunctionalTester $i): void
    {
        $result = ($this->handler)(new Envelope(
            message: new Command(
                userId: $this->userId,
                players: [
                    ['mate_id' => (string) $this->mate1Id],
                ],
                startedAt: new DateTime('2024-06-15 20:00:00'),
                finishedAt: new DateTime('2024-06-15 22:00:00'),
            ),
            messageId: 'msg-open-finished',
        ));

        $i->assertInstanceOf(Result::class, $result);

        $play = $this->plays->find($result->id);
        $i->assertNotNull($play);
        $i->assertSame(PlayStatus::Draft, $play->getStatus());
        $i->assertNotNull($play->getFinishedAt());
    }

    public function testOpenSessionWithSystemMate(FunctionalTester $i): void
    {
        $systemMateId = $this->uuidGenerator->generate();
        $this->mates->add(Mate::createSystem(
            $systemMateId,
            'Anonymous',
            new DateTime('now'),
        ));

        $result = ($this->handler)(new Envelope(
            message: new Command(
                userId: $this->userId,
                players: [
                    ['mate_id' => (string) $this->mate1Id],
                    ['mate_id' => (string) $systemMateId],
                ],
            ),
            messageId: 'msg-open-system-mate',
        ));

        $i->assertInstanceOf(Result::class, $result);
        $i->assertNotEmpty($result->id);
        $i->assertCount(2, $result->players);
    }

    public function testOpenSessionFailsWithNonExistentMate(FunctionalTester $i): void
    {
        $fakeMateId = 'non-existent-mate-' . uniqid();

        $i->expectThrowable(
            new NotFoundException('Mate not found: ' . $fakeMateId),
            fn () => ($this->handler)(new Envelope(
                message: new Command(
                    userId: $this->userId,
                    players: [
                        ['mate_id' => $fakeMateId],
                    ],
                ),
                messageId: 'msg-open-no-mate',
            )),
        );
    }

    public function testOpenSessionFailsWithMateBelongingToOtherUser(FunctionalTester $i): void
    {
        $otherUserId = $this->uuidGenerator->generate();
        $otherMateId = $this->uuidGenerator->generate();

        $this->mates->add(Mate::create(
            $otherMateId,
            $otherUserId,
            'Charlie',
            null,
            new DateTime(),
        ));

        $i->expectThrowable(
            new MateNotOwnedByUserException(),
            fn () => ($this->handler)(new Envelope(
                message: new Command(
                    userId: $this->userId,
                    players: [
                        ['mate_id' => (string) $otherMateId],
                    ],
                ),
                messageId: 'msg-open-other-user',
            )),
        );
    }

    public function testOpenSessionFailsWithDeletedMate(FunctionalTester $i): void
    {
        $deletedMateId = $this->uuidGenerator->generate();
        $mate = Mate::create(
            $deletedMateId,
            $this->userId,
            'Deleted Mate',
            null,
            new DateTime(),
        );
        $mate->softDelete(new DateTime());
        $this->mates->add($mate);

        $i->expectThrowable(
            new NotFoundException('Mate is deleted: ' . (string) $deletedMateId),
            fn () => ($this->handler)(new Envelope(
                message: new Command(
                    userId: $this->userId,
                    players: [
                        ['mate_id' => (string) $deletedMateId],
                    ],
                ),
                messageId: 'msg-open-deleted-mate',
            )),
        );
    }

    public function testOpenSessionFailsWithInvalidStartedAt(FunctionalTester $i): void
    {
        $i->expectThrowable(
            \Exception::class,
            static fn () => new DateTime('not-a-date'),
        );
    }

    public function testOpenSessionFailsWithInvalidFinishedAt(FunctionalTester $i): void
    {
        $i->expectThrowable(
            \Exception::class,
            static fn () => new DateTime('not-a-date'),
        );
    }

    public function testOpenSessionFailsWhenFinishedAtBeforeStartedAt(FunctionalTester $i): void
    {
        $i->expectThrowable(
            new FinishedAtBeforeStartedAtException(),
            fn () => ($this->handler)(new Envelope(
                message: new Command(
                    userId: $this->userId,
                    players: [
                        ['mate_id' => (string) $this->mate1Id],
                    ],
                    startedAt: new DateTime('2024-06-15 22:00:00'),
                    finishedAt: new DateTime('2024-06-15 20:00:00'),
                ),
                messageId: 'msg-open-finished-before-started',
            )),
        );
    }

    public function testOpenSessionFailsWithNonExistentGame(FunctionalTester $i): void
    {
        $i->expectThrowable(
            new NotFoundException('Game not found'),
            fn () => ($this->handler)(new Envelope(
                message: new Command(
                    userId: $this->userId,
                    players: [
                        ['mate_id' => (string) $this->mate1Id],
                    ],
                    gameId: new Uuid('00000000-0000-4000-8000-000000000002'),
                ),
                messageId: 'msg-open-game-not-found',
            )),
        );
    }

    public function testOpenSessionFailsWithDuplicateMateId(FunctionalTester $i): void
    {
        $mateIdStr = (string) $this->mate1Id;

        $i->expectThrowable(
            new DuplicatePlayerException(),
            fn () => ($this->handler)(new Envelope(
                message: new Command(
                    userId: $this->userId,
                    players: [
                        ['mate_id' => $mateIdStr],
                        ['mate_id' => $mateIdStr],
                    ],
                ),
                messageId: 'msg-open-duplicate',
            )),
        );
    }
}
