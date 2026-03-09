<?php

declare(strict_types=1);

namespace Bgl\Tests\Functional\Plays;

use Bgl\Application\Handlers\Plays\CreatePlay;
use Bgl\Application\Handlers\Plays\FinalizePlay;
use Bgl\Application\Handlers\Plays\ListPlays;
use Bgl\Application\Handlers\Plays\UpdatePlay;
use Bgl\Core\Auth\AuthenticationException;
use Bgl\Core\Identity\UuidGenerator;
use Bgl\Core\Messages\Envelope;
use Bgl\Core\ValueObjects\DateTime;
use Bgl\Core\ValueObjects\Uuid;
use Bgl\Domain\Games\Game;
use Bgl\Domain\Games\Games;
use Bgl\Domain\Mates\Mate;
use Bgl\Domain\Mates\Mates;
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
    private UpdatePlay\Handler $updateHandler;
    private FinalizePlay\Handler $finalizeHandler;
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

        /** @var UpdatePlay\Handler $updateHandler */
        $this->updateHandler = $container->get(UpdatePlay\Handler::class);

        /** @var FinalizePlay\Handler $finalizeHandler */
        $this->finalizeHandler = $container->get(FinalizePlay\Handler::class);

        /** @var Mates $mates */
        $this->mates = $container->get(Mates::class);

        /** @var Games $games */
        $this->games = $container->get(Games::class);

        /** @var UuidGenerator $uuidGenerator */
        $this->uuidGenerator = $container->get(UuidGenerator::class);

        $this->userId = $this->uuidGenerator->generate();

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
        $otherUserId = $this->uuidGenerator->generate();
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

    public function testListPlaysByAuthorIdShowsPublicOnly(FunctionalTester $i): void
    {
        $otherUserId = $this->uuidGenerator->generate();
        $otherMateId = $this->uuidGenerator->generate();
        $this->mates->add(Mate::create($otherMateId, $otherUserId, 'Charlie', null, new DateTime()));

        $publicId = $this->createPlayForUser($otherUserId, 'Public session');
        $this->publishPlay($publicId, $otherUserId, 'public', 'Public session');

        $linkId = $this->createPlayForUser($otherUserId, 'Link session');
        $this->publishPlay($linkId, $otherUserId, 'link', 'Link session');

        $authId = $this->createPlayForUser($otherUserId, 'Auth session');
        $this->publishPlay($authId, $otherUserId, 'authenticated', 'Auth session');

        $privateId = $this->createPlayForUser($otherUserId, 'Private session');
        $this->publishPlay($privateId, $otherUserId, 'private', 'Private session');

        $result = ($this->handler)(new Envelope(
            message: new ListPlays\Query(
                userId: (string)$this->userId,
                authorId: (string)$otherUserId,
            ),
            messageId: 'msg-list-author-public',
        ));

        $i->assertSame(3, $result->total);
        $names = array_column($result->data, 'name');
        $i->assertContains('Public session', $names);
        $i->assertContains('Link session', $names);
        $i->assertContains('Auth session', $names);
        $i->assertNotContains('Private session', $names);
    }

    public function testListPlaysByAuthorIdHidesPrivateAndDeleted(FunctionalTester $i): void
    {
        $otherUserId = $this->uuidGenerator->generate();
        $otherMateId = $this->uuidGenerator->generate();
        $this->mates->add(Mate::create($otherMateId, $otherUserId, 'Dave', null, new DateTime()));

        // Private session (default visibility) -- not visible to others
        $this->createPlayForUser($otherUserId, 'Private session');

        $publicId = $this->createPlayForUser($otherUserId, 'Public session');
        $this->publishPlay($publicId, $otherUserId, 'public', 'Public session');

        $result = ($this->handler)(new Envelope(
            message: new ListPlays\Query(
                userId: (string)$this->userId,
                authorId: (string)$otherUserId,
            ),
            messageId: 'msg-list-author-no-private',
        ));

        $i->assertSame(1, $result->total);
        $i->assertSame('Public session', $result->data[0]['name']);
    }

    public function testListPlaysByAuthorIdSelfShowsAll(FunctionalTester $i): void
    {
        $this->createPlay('My draft');

        $publishedId = $this->createPlayReturningId('My published');
        $this->publishPlay($publishedId, $this->userId, 'public', 'My published');

        $result = ($this->handler)(new Envelope(
            message: new ListPlays\Query(
                userId: (string)$this->userId,
                authorId: (string)$this->userId,
            ),
            messageId: 'msg-list-author-self',
        ));

        $i->assertSame(2, $result->total);
    }

    public function testListPlaysWithoutAuthAndWithoutAuthorIdThrows(FunctionalTester $i): void
    {
        $i->expectThrowable(AuthenticationException::class, function (): void {
            ($this->handler)(new Envelope(
                message: new ListPlays\Query(),
                messageId: 'msg-list-no-auth',
            ));
        });
    }

    public function testListPlaysByAuthorIdWithoutAuthShowsPublicAndLinkOnly(FunctionalTester $i): void
    {
        $otherUserId = $this->uuidGenerator->generate();
        $otherMateId = $this->uuidGenerator->generate();
        $this->mates->add(Mate::create($otherMateId, $otherUserId, 'Eve', null, new DateTime()));

        $publicId = $this->createPlayForUser($otherUserId, 'Public play');
        $this->publishPlay($publicId, $otherUserId, 'public', 'Public play');

        $authId = $this->createPlayForUser($otherUserId, 'Auth play');
        $this->publishPlay($authId, $otherUserId, 'authenticated', 'Auth play');

        $result = ($this->handler)(new Envelope(
            message: new ListPlays\Query(
                authorId: (string)$otherUserId,
            ),
            messageId: 'msg-list-author-noauth',
        ));

        $i->assertSame(1, $result->total);
        $i->assertSame('Public play', $result->data[0]['name']);
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

    private function createPlayReturningId(string $name, ?string $gameId = null): Uuid
    {
        $result = ($this->createHandler)(new Envelope(
            message: new CreatePlay\Command(
                userId: $this->userId,
                name: $name,
                gameId: $gameId !== null ? new Uuid($gameId) : null,
            ),
            messageId: 'msg-create-' . uniqid(),
        ));

        return new Uuid($result->id);
    }

    private function createPlayForUser(Uuid $userId, string $name): Uuid
    {
        $result = ($this->createHandler)(new Envelope(
            message: new CreatePlay\Command(
                userId: $userId,
                name: $name,
            ),
            messageId: 'msg-create-' . uniqid(),
        ));

        return new Uuid($result->id);
    }

    private function publishPlay(Uuid $sessionId, Uuid $userId, string $visibility, string $name = ''): void
    {
        ($this->finalizeHandler)(new Envelope(
            message: new FinalizePlay\Command(
                sessionId: $sessionId,
                userId: $userId,
            ),
            messageId: 'msg-finalize-' . uniqid(),
        ));

        ($this->updateHandler)(new Envelope(
            message: new UpdatePlay\Command(
                sessionId: $sessionId,
                userId: $userId,
                name: $name,
                visibility: $visibility,
            ),
            messageId: 'msg-update-' . uniqid(),
        ));
    }
}
