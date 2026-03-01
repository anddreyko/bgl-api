<?php

declare(strict_types=1);

namespace Bgl\Tests\Benchmark\Handlers;

use Bgl\Application\Handlers\Games\GetGame;
use Bgl\Application\Handlers\Games\SearchGames;
use Bgl\Core\Messages\Envelope;
use Bgl\Core\ValueObjects\DateTime;
use Bgl\Core\ValueObjects\Uuid;
use Bgl\Domain\Games\Game;
use Bgl\Domain\Games\Games;
use Bgl\Tests\Benchmark\BenchHelper;
use PhpBench\Attributes as Bench;

#[Bench\BeforeMethods('setUp')]
#[Bench\AfterMethods('tearDown')]
final class GamesBench
{
    private SearchGames\Handler $searchHandler;
    private GetGame\Handler $getHandler;
    private Games $games;

    public function setUp(): void
    {
        $this->searchHandler = BenchHelper::get(SearchGames\Handler::class);
        $this->getHandler = BenchHelper::get(GetGame\Handler::class);
        $this->games = BenchHelper::get(Games::class);

        BenchHelper::clearRepositories();
    }

    public function tearDown(): void
    {
        BenchHelper::clearRepositories();
    }

    #[Bench\Revs(50)]
    #[Bench\Iterations(5)]
    public function benchSearchGames(): void
    {
        for ($i = 0; $i < 50; ++$i) {
            $this->seedGame("search-game-{$i}", "Catan Expansion {$i}");
        }

        ($this->searchHandler)(new Envelope(
            message: new SearchGames\Query(q: 'Catan'),
            messageId: 'bench-search-games',
        ));

        BenchHelper::clearRepositories();
    }

    #[Bench\Revs(100)]
    #[Bench\Iterations(5)]
    public function benchGetGame(): void
    {
        $this->seedGame('bench-get-game', 'Catan');

        ($this->getHandler)(new Envelope(
            message: new GetGame\Query(gameId: 'bench-get-game'),
            messageId: 'bench-get-game',
        ));
    }

    private function seedGame(string $id, string $name): void
    {
        $this->games->add(Game::create(
            new Uuid($id),
            bggId: 13,
            name: $name,
            yearPublished: 1995,
            createdAt: new DateTime('now'),
        ));
    }
}
