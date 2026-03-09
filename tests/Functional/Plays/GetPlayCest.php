<?php

declare(strict_types=1);

namespace Bgl\Tests\Functional\Plays;

use Bgl\Application\Handlers\Plays\CreatePlay\Command as CreatePlayCommand;
use Bgl\Application\Handlers\Plays\CreatePlay\Handler as CreatePlayHandler;
use Bgl\Application\Handlers\Plays\GetPlay\Handler;
use Bgl\Application\Handlers\Plays\GetPlay\Query;
use Bgl\Application\Handlers\Plays\GetPlay\Result;
use Bgl\Core\Auth\AuthenticationException;
use Bgl\Core\Exceptions\NotFoundException;
use Bgl\Core\Identity\UuidGenerator;
use Bgl\Core\Messages\Envelope;
use Bgl\Core\ValueObjects\DateTime;
use Bgl\Core\ValueObjects\Uuid;
use Bgl\Domain\Mates\Mate;
use Bgl\Domain\Mates\Mates;
use Bgl\Domain\Plays\Play;
use Bgl\Domain\Plays\Plays;
use Bgl\Domain\Plays\Visibility;
use Bgl\Tests\Support\DiHelper;
use Bgl\Tests\Support\FunctionalTester;
use Codeception\Attribute\Group;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @covers \Bgl\Application\Handlers\Plays\GetPlay\Handler
 */
#[Group('application', 'handler', 'plays', 'get-play')]
final class GetPlayCest
{
    private EntityManagerInterface $em;
    private Handler $handler;
    private CreatePlayHandler $createHandler;
    private Plays $plays;
    private Mates $mates;
    private UuidGenerator $uuidGenerator;

    private Uuid $ownerUserId;
    private Uuid $otherUserId;
    private Uuid $ownerMateId;
    private Uuid $otherMateId;

    public function _before(): void
    {
        $container = DiHelper::container();

        /** @var EntityManagerInterface $em */
        $this->em = $container->get(EntityManagerInterface::class);

        /** @var Handler $handler */
        $this->handler = $container->get(Handler::class);

        /** @var CreatePlayHandler $createHandler */
        $this->createHandler = $container->get(CreatePlayHandler::class);

        /** @var Plays $plays */
        $this->plays = $container->get(Plays::class);

        /** @var Mates $mates */
        $this->mates = $container->get(Mates::class);

        /** @var UuidGenerator $uuidGenerator */
        $this->uuidGenerator = $container->get(UuidGenerator::class);

        $this->ownerUserId = $this->uuidGenerator->generate();
        $this->otherUserId = $this->uuidGenerator->generate();

        $this->ownerMateId = $this->uuidGenerator->generate();
        $this->otherMateId = $this->uuidGenerator->generate();

        $this->mates->add(Mate::create(
            $this->ownerMateId,
            $this->ownerUserId,
            'Owner Mate',
            null,
            new DateTime(),
        ));

        // Mate belonging to otherUser -- used for friends visibility test
        $this->mates->add(Mate::create(
            $this->otherMateId,
            $this->otherUserId,
            'Other Mate',
            null,
            new DateTime(),
        ));
    }

    public function testOwnerViewsPrivateSession(FunctionalTester $i): void
    {
        $sessionId = $this->createSession(visibility: 'private');

        $result = $this->getPlay($sessionId, (string)$this->ownerUserId);

        $i->assertInstanceOf(Result::class, $result);
        $i->assertSame($sessionId, $result->id);
        $i->assertIsArray($result->author);
        $i->assertSame((string)$this->ownerUserId, $result->author['id']);
        $i->assertSame('private', $result->visibility);
    }

    public function testNonOwnerDeniedPrivateSession(FunctionalTester $i): void
    {
        $sessionId = $this->createSession(visibility: 'private');

        $i->expectThrowable(NotFoundException::class, fn() => $this->getPlay($sessionId, (string)$this->otherUserId));
    }

    public function testAnonymousViewsPublicSession(FunctionalTester $i): void
    {
        $sessionId = $this->createSession(visibility: 'public');
        $this->finalizeSession($sessionId);

        $result = $this->getPlay($sessionId, null);

        $i->assertInstanceOf(Result::class, $result);
        $i->assertSame($sessionId, $result->id);
        $i->assertSame('public', $result->visibility);
    }

    public function testAnonymousViewsLinkSession(FunctionalTester $i): void
    {
        $sessionId = $this->createSession(visibility: 'link');
        $this->finalizeSession($sessionId);

        $result = $this->getPlay($sessionId, null);

        $i->assertInstanceOf(Result::class, $result);
        $i->assertSame($sessionId, $result->id);
        $i->assertSame('link', $result->visibility);
    }

    public function testAnonymousDeniedAuthenticatedSession(FunctionalTester $i): void
    {
        $sessionId = $this->createSession(visibility: 'authenticated');
        $this->finalizeSession($sessionId);

        $i->expectThrowable(AuthenticationException::class, fn() => $this->getPlay($sessionId, null));
    }

    public function testAuthenticatedViewsAuthenticatedSession(FunctionalTester $i): void
    {
        $sessionId = $this->createSession(visibility: 'authenticated');
        $this->finalizeSession($sessionId);

        $result = $this->getPlay($sessionId, (string)$this->otherUserId);

        $i->assertInstanceOf(Result::class, $result);
        $i->assertSame($sessionId, $result->id);
    }

    public function testParticipantsVisibilityPlayerAccess(FunctionalTester $i): void
    {
        // Session owned by ownerUser, with ownerMateId as player
        // ownerMateId belongs to ownerUserId
        // ownerUserId can view because their mate is a player
        $sessionId = $this->createSession(
            visibility: 'participants',
            players: [['mate_id' => (string)$this->ownerMateId]],
        );
        $this->finalizeSession($sessionId);

        $result = $this->getPlay($sessionId, (string)$this->ownerUserId);

        $i->assertInstanceOf(Result::class, $result);
        $i->assertSame($sessionId, $result->id);
    }

    public function testAnonymousDeniedParticipantsSession(FunctionalTester $i): void
    {
        $sessionId = $this->createSession(
            visibility: 'participants',
            players: [['mate_id' => (string)$this->ownerMateId]],
        );
        $this->finalizeSession($sessionId);

        // Until MATES-002 (mate-to-user linking), participants = owner-only
        $i->expectThrowable(NotFoundException::class, fn() => $this->getPlay($sessionId, null));
    }

    public function testParticipantsVisibilityNonPlayerDenied(FunctionalTester $i): void
    {
        // Session owned by ownerUser, with ownerMateId as player
        // ownerMateId belongs to ownerUser (not otherUser)
        // otherUser should NOT be able to view
        $sessionId = $this->createSession(
            visibility: 'participants',
            players: [['mate_id' => (string)$this->ownerMateId]],
        );
        $this->finalizeSession($sessionId);

        $i->expectThrowable(NotFoundException::class, fn() => $this->getPlay($sessionId, (string)$this->otherUserId));
    }

    public function testNonExistentSessionThrowsNotFound(FunctionalTester $i): void
    {
        $i->expectThrowable(
            NotFoundException::class,
            fn() => $this->getPlay('non-existent-session-id', (string)$this->ownerUserId),
        );
    }

    public function testCurrentSessionPublicVisibleToAll(FunctionalTester $i): void
    {
        // Current (not finalized) session with public visibility -- visible to everyone
        $sessionId = $this->createSession(visibility: 'public');

        // Owner can view
        $result = $this->getPlay($sessionId, (string)$this->ownerUserId);
        $i->assertInstanceOf(Result::class, $result);

        // Non-owner can also view (public visibility)
        $result = $this->getPlay($sessionId, (string)$this->otherUserId);
        $i->assertInstanceOf(Result::class, $result);
    }

    public function testCurrentSessionPrivateOwnerOnly(FunctionalTester $i): void
    {
        // Current session with private visibility -- owner only
        $sessionId = $this->createSession(visibility: 'private');

        $result = $this->getPlay($sessionId, (string)$this->ownerUserId);
        $i->assertInstanceOf(Result::class, $result);

        $i->expectThrowable(NotFoundException::class, fn() => $this->getPlay($sessionId, (string)$this->otherUserId));
    }

    public function testAnonymousViewsCurrentPublicSession(FunctionalTester $i): void
    {
        $sessionId = $this->createSession(visibility: 'public');

        $result = $this->getPlay($sessionId, null);
        $i->assertInstanceOf(Result::class, $result);
    }

    public function testResultContainsPlayerData(FunctionalTester $i): void
    {
        $sessionId = $this->createSession(
            visibility: 'private',
            players: [
                ['mate_id' => (string)$this->ownerMateId, 'score' => 10, 'is_winner' => true, 'color' => 'red'],
            ],
        );

        $result = $this->getPlay($sessionId, (string)$this->ownerUserId);

        $i->assertInstanceOf(Result::class, $result);
        $i->assertCount(1, $result->players);
        $i->assertSame((string)$this->ownerMateId, $result->players[0]['mate_id']);
        $i->assertSame(10, $result->players[0]['score']);
        $i->assertTrue($result->players[0]['is_winner']);
        $i->assertSame('red', $result->players[0]['color']);
    }

    /**
     * @param list<array{mate_id: string, score?: int, is_winner?: bool, color?: string}> $players
     */
    private function createSession(
        string $visibility = 'private',
        array $players = [],
        ?Uuid $userId = null,
    ): string {
        $actualUserId = $userId ?? $this->ownerUserId;

        $result = ($this->createHandler)(new Envelope(
            message: new CreatePlayCommand(
                userId: $actualUserId,
                name: 'Test session',
                players: $players,
                startedAt: new DateTime('2024-06-15 20:00:00'),
                visibility: $visibility,
            ),
            messageId: 'msg-get-' . uniqid(),
        ));

        return $result->id;
    }

    private function finalizeSession(string $sessionId): void
    {
        /** @var Play|null $play */
        $play = $this->plays->find($sessionId);
        if ($play === null) {
            return;
        }
        $play->finalize(new DateTime('2024-06-15 22:00:00'));
        $this->em->flush();
    }

    private function getPlay(string $playId, ?string $userId): Result
    {
        /** @var Result $result */
        $result = ($this->handler)(new Envelope(
            message: new Query(playId: $playId, userId: $userId),
            messageId: 'msg-get-play-' . uniqid(),
        ));

        return $result;
    }
}
