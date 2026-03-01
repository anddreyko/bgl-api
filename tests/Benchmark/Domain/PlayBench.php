<?php

declare(strict_types=1);

namespace Bgl\Tests\Benchmark\Domain;

use Bgl\Core\ValueObjects\DateTime;
use Bgl\Core\ValueObjects\Uuid;
use Bgl\Domain\Plays\Play;
use Bgl\Domain\Plays\Player\Player;
use Bgl\Domain\Plays\Visibility;
use Bgl\Infrastructure\Persistence\InMemory\InMemoryPlayers;
use PhpBench\Attributes as Bench;

final class PlayBench
{
    #[Bench\Revs(1000)]
    #[Bench\Iterations(5)]
    public function benchCreatePlay(): void
    {
        Play::create(
            id: new Uuid('bench-play-id'),
            userId: new Uuid('bench-user-id'),
            name: 'Bench session',
            startedAt: new DateTime('2024-06-15 20:00:00'),
            players: new InMemoryPlayers(),
        );
    }

    #[Bench\Revs(1000)]
    #[Bench\Iterations(5)]
    public function benchAddPlayer(): void
    {
        $play = Play::create(
            id: new Uuid('bench-play-id'),
            userId: new Uuid('bench-user-id'),
            name: 'Bench session',
            startedAt: new DateTime('2024-06-15 20:00:00'),
            players: new InMemoryPlayers(),
        );

        Player::create(
            id: new Uuid('bench-player-id'),
            play: $play,
            mateId: new Uuid('bench-mate-id'),
            score: 42,
            isWinner: true,
            color: 'red',
        );
    }

    /**
     * @param array{count: int} $params
     */
    #[Bench\Revs(100)]
    #[Bench\Iterations(5)]
    #[Bench\ParamProviders('providePlayerCounts')]
    public function benchAddMultiplePlayers(array $params): void
    {
        $play = Play::create(
            id: new Uuid('bench-play-id'),
            userId: new Uuid('bench-user-id'),
            name: 'Bench session',
            startedAt: new DateTime('2024-06-15 20:00:00'),
            players: new InMemoryPlayers(),
        );

        for ($i = 0; $i < $params['count']; ++$i) {
            $play->addPlayer(Player::create(
                id: new Uuid("player-{$i}"),
                play: $play,
                mateId: new Uuid("mate-{$i}"),
                score: $i * 10,
                isWinner: $i === 0,
                color: null,
            ));
        }
    }

    /**
     * @return \Generator<string, array{count: int}>
     */
    public function providePlayerCounts(): \Generator
    {
        yield '1 player' => ['count' => 1];
        yield '5 players' => ['count' => 5];
        yield '10 players' => ['count' => 10];
    }

    #[Bench\Revs(1000)]
    #[Bench\Iterations(5)]
    public function benchFinalizePlay(): void
    {
        $play = Play::create(
            id: new Uuid('bench-play-id'),
            userId: new Uuid('bench-user-id'),
            name: 'Bench session',
            startedAt: new DateTime('2024-06-15 20:00:00'),
            players: new InMemoryPlayers(),
        );

        $play->finalize(new DateTime('2024-06-15 22:00:00'));
    }

    #[Bench\Revs(1000)]
    #[Bench\Iterations(5)]
    public function benchUpdatePlay(): void
    {
        $play = Play::create(
            id: new Uuid('bench-play-id'),
            userId: new Uuid('bench-user-id'),
            name: 'Bench session',
            startedAt: new DateTime('2024-06-15 20:00:00'),
            players: new InMemoryPlayers(),
        );

        $play->update('Updated name', null, Visibility::Public);
    }
}
