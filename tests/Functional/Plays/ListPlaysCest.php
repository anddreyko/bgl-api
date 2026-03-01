<?php

declare(strict_types=1);

namespace Bgl\Tests\Functional\Plays;

use Bgl\Application\Handlers\Plays\CreatePlay;
use Bgl\Application\Handlers\Plays\ListPlays;
use Bgl\Core\Identity\UuidGenerator;
use Bgl\Core\Messages\Envelope;
use Bgl\Core\ValueObjects\DateTime;
use Bgl\Core\ValueObjects\Uuid;
use Bgl\Domain\Games\Entities\Game;
use Bgl\Domain\Games\Entities\Games;
use Bgl\Domain\Mates\Entities\Mate;
use Bgl\Domain\Mates\Entities\Mates;
use Bgl\Tests\Support\DiHelper;
use Bgl\Tests\Support\FunctionalTester;
use Codeception\Attribute\Group;

/**
 * @covers \Bgl\Application\Handlers\Plays\ListPlays\Handler
 */
#[Group('application', 'handler', 'plays', 'list-plays')]
final class ListPlaysCest
{
    private ListPlays\Handler $handler;
    private CreatePlay\Handler $createHandler;
    private Mates $mates;
    private Games $games;
    private UuidGenerator $uuidGenerator;

    private Uuid $userId;
    private Uuid $mate1Id;
    private Uuid $gameId;
    private Uuid $game2Id;

    public function _before(): void
    {
        $container = DiHelper::container();

        /** @var ListPlays\Handler $handler */
        $this->handler = $container->get(ListPlays\Handler::class);

        /** @var CreatePlay\Handler $createHandler */
        $this->createHandler = $container->get(CreatePlay\Handler::class);

        /** @var Mates $mates */
        $this->mates = $container->get(Mates::class);

        /** @var Games $games */
        $this->games = $container->get(Games::class);

        /** @var UuidGenerator $uuidGenerator */
        $this->uuidGenerator = $container->get(UuidGenerator::class);

        $this->userId = new Uuid('user-list-' . uniqid());

        $this->mate1Id = $this->uuidGenerator->generate();
        $this->mates->add(Mate::create(
            $this->mate1Id,
            $this->userId,
            'Alice',
            null,
            new DateTime(),
        ));

        $this->gameId = $this->uuidGenerator->generate();
        $this->games->add(Game::create(
            $this->gameId,
            11111,
            'Catan',
            1995,
            new DateTime(),
        ));

        $this->game2Id = $this->uuidGenerator->generate();
        $this->games->add(Game::create(
            $this->game2Id,
            22222,
            'Ticket to Ride',
            2004,
            new DateTime(),
        ));
    }

    public function testListPlaysReturnsUserSessions(FunctionalTester $i): void
    {
        $this->createPlay('Session 1');
        $this->createPlay('Session 2');
        $this->createPlay('Session 3');

        $result = ($this->handler)(new Envelope(
            message: new ListPlays\Query(
                userId: (string)$this->userId,
            ),
            messageId: 'msg-list-all',
        ));

        $i->assertInstanceOf(ListPlays\Result::class, $result);
        $i->assertCount(3, $result->data);
        $i->assertSame(3, $result->total);
        $i->assertSame(1, $result->page);
        $i->assertSame(20, $result->size);
    }

    public function testListPlaysWithPagination(FunctionalTester $i): void
    {
        for ($n = 1; $n <= 5; $n++) {
            $this->createPlay('Session ' . $n);
        }

        $result = ($this->handler)(new Envelope(
            message: new ListPlays\Query(
                userId: (string)$this->userId,
                page: 1,
                size: 2,
            ),
            messageId: 'msg-list-page',
        ));

        $i->assertCount(2, $result->data);
        $i->assertSame(5, $result->total);
        $i->assertSame(1, $result->page);
        $i->assertSame(2, $result->size);
    }

    public function testListPlaysFilterByGameId(FunctionalTester $i): void
    {
        $this->createPlay('Catan game', (string)$this->gameId);
        $this->createPlay('Ticket game', (string)$this->game2Id);
        $this->createPlay('No game');

        $result = ($this->handler)(new Envelope(
            message: new ListPlays\Query(
                userId: (string)$this->userId,
                gameId: (string)$this->gameId,
            ),
            messageId: 'msg-list-game',
        ));

        $i->assertCount(1, $result->data);
        $i->assertSame(1, $result->total);
        $i->assertSame('Catan game', $result->data[0]['name']);
    }

    public function testListPlaysFilterByDateRange(FunctionalTester $i): void
    {
        $this->createPlay('Old session', null, '2024-01-01 10:00:00');
        $this->createPlay('Mid session', null, '2024-06-15 10:00:00');
        $this->createPlay('New session', null, '2024-12-01 10:00:00');

        $result = ($this->handler)(new Envelope(
            message: new ListPlays\Query(
                userId: (string)$this->userId,
                from: '2024-03-01 00:00:00',
                to: '2024-09-01 00:00:00',
            ),
            messageId: 'msg-list-date',
        ));

        $i->assertCount(1, $result->data);
        $i->assertSame(1, $result->total);
        $i->assertSame('Mid session', $result->data[0]['name']);
    }

    public function testListPlaysEmptyResult(FunctionalTester $i): void
    {
        $result = ($this->handler)(new Envelope(
            message: new ListPlays\Query(
                userId: (string)$this->userId,
            ),
            messageId: 'msg-list-empty',
        ));

        $i->assertSame([], $result->data);
        $i->assertSame(0, $result->total);
    }

    public function testListPlaysDoesNotShowOtherUserSessions(FunctionalTester $i): void
    {
        $otherUserId = new Uuid('other-user-' . uniqid());
        $otherMateId = $this->uuidGenerator->generate();
        $this->mates->add(Mate::create(
            $otherMateId,
            $otherUserId,
            'Bob',
            null,
            new DateTime(),
        ));

        // Create session for current user
        $this->createPlay('My session');

        // Create session for other user
        ($this->createHandler)(new Envelope(
            message: new CreatePlay\Command(
                userId: $otherUserId,
                name: 'Other session',
            ),
            messageId: 'msg-create-other',
        ));

        $result = ($this->handler)(new Envelope(
            message: new ListPlays\Query(
                userId: (string)$this->userId,
            ),
            messageId: 'msg-list-isolation',
        ));

        $i->assertCount(1, $result->data);
        $i->assertSame('My session', $result->data[0]['name']);
    }

    public function testListPlaysSortedByStartedAtDesc(FunctionalTester $i): void
    {
        $this->createPlay('First', null, '2024-01-01 10:00:00');
        $this->createPlay('Second', null, '2024-06-01 10:00:00');
        $this->createPlay('Third', null, '2024-12-01 10:00:00');

        $result = ($this->handler)(new Envelope(
            message: new ListPlays\Query(
                userId: (string)$this->userId,
            ),
            messageId: 'msg-list-sort',
        ));

        $i->assertCount(3, $result->data);
        $i->assertSame('Third', $result->data[0]['name']);
        $i->assertSame('Second', $result->data[1]['name']);
        $i->assertSame('First', $result->data[2]['name']);
    }

    public function testListPlaysIncludesGameInfo(FunctionalTester $i): void
    {
        $this->createPlay('With game', (string)$this->gameId);

        $result = ($this->handler)(new Envelope(
            message: new ListPlays\Query(
                userId: (string)$this->userId,
            ),
            messageId: 'msg-list-game-info',
        ));

        $i->assertCount(1, $result->data);
        $i->assertNotNull($result->data[0]['game']);
        $i->assertSame((string)$this->gameId, $result->data[0]['game']['id']);
        $i->assertSame('Catan', $result->data[0]['game']['name']);
    }

    public function testListPlaysIncludesPlayerInfo(FunctionalTester $i): void
    {
        ($this->createHandler)(new Envelope(
            message: new CreatePlay\Command(
                userId: $this->userId,
                name: 'With players',
                players: [
                    ['mate_id' => (string)$this->mate1Id, 'score' => 10, 'is_winner' => true, 'color' => 'red'],
                ],
            ),
            messageId: 'msg-create-with-players',
        ));

        $result = ($this->handler)(new Envelope(
            message: new ListPlays\Query(
                userId: (string)$this->userId,
            ),
            messageId: 'msg-list-players',
        ));

        $i->assertCount(1, $result->data);
        $i->assertNotEmpty($result->data[0]['players']);
        $i->assertSame((string)$this->mate1Id, $result->data[0]['players'][0]['mate_id']);
        $i->assertSame(10, $result->data[0]['players'][0]['score']);
        $i->assertTrue($result->data[0]['players'][0]['is_winner']);
        $i->assertSame('red', $result->data[0]['players'][0]['color']);
    }

    private function createPlay(
        string $name,
        ?string $gameId = null,
        ?string $startedAt = null,
    ): void {
        ($this->createHandler)(new Envelope(
            message: new CreatePlay\Command(
                userId: $this->userId,
                name: $name,
                gameId: $gameId !== null ? new Uuid($gameId) : null,
                startedAt: $startedAt !== null ? new DateTime($startedAt) : null,
            ),
            messageId: 'msg-create-' . uniqid(),
        ));
    }
}
