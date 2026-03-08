<?php

declare(strict_types=1);

namespace Bgl\Tests\Benchmark\Handlers;

use Bgl\Application\Handlers\Plays\CreatePlay;
use Bgl\Application\Handlers\Plays\FinalizePlay;
use Bgl\Application\Handlers\Plays\GetPlay;
use Bgl\Application\Handlers\Plays\ListPlays;
use Bgl\Application\Handlers\Plays\UpdatePlay;
use Bgl\Core\Messages\Envelope;
use Bgl\Core\ValueObjects\DateTime;
use Bgl\Core\ValueObjects\Uuid;
use Bgl\Domain\Mates\Mate;
use Bgl\Domain\Mates\Mates;
use Bgl\Domain\Plays\Play;
use Bgl\Domain\Plays\Plays;
use Bgl\Infrastructure\Persistence\InMemory\InMemoryPlayers;
use Bgl\Tests\Benchmark\BenchHelper;
use PhpBench\Attributes as Bench;

#[Bench\BeforeMethods('setUp')]
#[Bench\AfterMethods('tearDown')]
final class PlaysBench
{
    private const string USER_ID = '00000000-0000-4000-8000-000000000060';

    private CreatePlay\Handler $createHandler;
    private GetPlay\Handler $getHandler;
    private ListPlays\Handler $listHandler;
    private FinalizePlay\Handler $finalizeHandler;
    private UpdatePlay\Handler $updateHandler;
    private Plays $plays;
    private Mates $mates;
    private int $counter = 0;

    public function setUp(): void
    {
        $this->createHandler = BenchHelper::get(CreatePlay\Handler::class);
        $this->getHandler = BenchHelper::get(GetPlay\Handler::class);
        $this->listHandler = BenchHelper::get(ListPlays\Handler::class);
        $this->finalizeHandler = BenchHelper::get(FinalizePlay\Handler::class);
        $this->updateHandler = BenchHelper::get(UpdatePlay\Handler::class);
        $this->plays = BenchHelper::get(Plays::class);
        $this->mates = BenchHelper::get(Mates::class);

        $this->counter = 0;
        BenchHelper::clearRepositories();
    }

    public function tearDown(): void
    {
        BenchHelper::clearRepositories();
    }

    #[Bench\Revs(100)]
    #[Bench\Iterations(5)]
    public function benchCreatePlay(): void
    {
        ++$this->counter;
        ($this->createHandler)(new Envelope(
            message: new CreatePlay\Command(
                userId: new Uuid(self::USER_ID),
                name: "Bench session {$this->counter}",
                startedAt: new DateTime('2024-06-15 20:00:00'),
            ),
            messageId: "bench-create-play-{$this->counter}",
        ));
    }

    #[Bench\Revs(50)]
    #[Bench\Iterations(5)]
    public function benchCreatePlayWithPlayers(): void
    {
        ++$this->counter;
        $players = [];
        for ($i = 0; $i < 5; ++$i) {
            $mateId = $this->seedMate("bench-mate-{$this->counter}-{$i}");
            $players[] = ['mate_id' => $mateId, 'score' => $i * 10, 'is_winner' => $i === 0, 'color' => 'blue'];
        }

        ($this->createHandler)(new Envelope(
            message: new CreatePlay\Command(
                userId: new Uuid(self::USER_ID),
                name: "Session with players {$this->counter}",
                players: $players,
                startedAt: new DateTime('2024-06-15 20:00:00'),
            ),
            messageId: "bench-create-play-players-{$this->counter}",
        ));
    }

    #[Bench\Revs(100)]
    #[Bench\Iterations(5)]
    public function benchGetPlay(): void
    {
        $play = $this->seedPlay('bench-get-play');

        ($this->getHandler)(new Envelope(
            message: new GetPlay\Query(
                playId: (string)$play->getId(),
                userId: self::USER_ID,
            ),
            messageId: 'bench-get-play',
        ));
    }

    #[Bench\Revs(50)]
    #[Bench\Iterations(5)]
    public function benchListPlays(): void
    {
        for ($i = 0; $i < 20; ++$i) {
            $this->seedPlay("bench-list-play-{$i}");
        }

        ($this->listHandler)(new Envelope(
            message: new ListPlays\Query(userId: self::USER_ID),
            messageId: 'bench-list-plays',
        ));

        BenchHelper::clearRepositories();
    }

    #[Bench\Revs(100)]
    #[Bench\Iterations(5)]
    public function benchFinalizePlay(): void
    {
        $play = $this->seedPlay('bench-finalize-play');

        ($this->finalizeHandler)(new Envelope(
            message: new FinalizePlay\Command(
                sessionId: $play->getId(),
                userId: new Uuid(self::USER_ID),
                finishedAt: new DateTime('2024-06-15 22:00:00'),
            ),
            messageId: 'bench-finalize-play',
        ));

        BenchHelper::clearRepositories();
    }

    #[Bench\Revs(100)]
    #[Bench\Iterations(5)]
    public function benchUpdatePlay(): void
    {
        $play = $this->seedPlay('bench-update-play');

        ($this->updateHandler)(new Envelope(
            message: new UpdatePlay\Command(
                sessionId: $play->getId(),
                userId: new Uuid(self::USER_ID),
                name: 'Updated session',
                visibility: 'public',
            ),
            messageId: 'bench-update-play',
        ));

        BenchHelper::clearRepositories();
    }

    private function seedPlay(string $label): Play
    {
        $play = Play::create(
            id: new Uuid(\Ramsey\Uuid\Uuid::uuid4()->toString()),
            userId: new Uuid(self::USER_ID),
            name: "Bench play {$label}",
            startedAt: new DateTime('2024-06-15 20:00:00'),
            players: new InMemoryPlayers(),
        );
        $this->plays->add($play);

        return $play;
    }

    private function seedMate(string $label): string
    {
        $id = \Ramsey\Uuid\Uuid::uuid4()->toString();
        $this->mates->add(Mate::create(
            new Uuid($id),
            new Uuid(self::USER_ID),
            "Mate {$label}",
            null,
            new DateTime('now'),
        ));

        return $id;
    }
}
