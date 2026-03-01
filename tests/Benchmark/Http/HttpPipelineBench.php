<?php

declare(strict_types=1);

namespace Bgl\Tests\Benchmark\Http;

use Bgl\Core\ValueObjects\DateTime;
use Bgl\Core\ValueObjects\Uuid;
use Bgl\Domain\Games\Game;
use Bgl\Domain\Games\Games;
use Bgl\Presentation\Api\ApiAction;
use Bgl\Tests\Benchmark\BenchHelper;
use PhpBench\Attributes as Bench;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Factory\ServerRequestFactory;

#[Bench\BeforeMethods('setUp')]
#[Bench\AfterMethods('tearDown')]
#[Bench\Assert('mode(variant.time.avg) <= mode(baseline.time.avg) +/- 15%')]
final class HttpPipelineBench
{
    private ApiAction $apiAction;
    private ServerRequestInterface $pingRequest;
    private ServerRequestInterface $searchRequest;

    public function setUp(): void
    {
        $this->apiAction = BenchHelper::get(ApiAction::class);

        $factory = new ServerRequestFactory();
        $this->pingRequest = $factory->createServerRequest('GET', '/ping');
        $this->searchRequest = $factory->createServerRequest('GET', '/v1/games/search?q=Catan');

        $games = BenchHelper::get(Games::class);
        for ($i = 0; $i < 50; ++$i) {
            $games->add(
                Game::create(
                    new Uuid("bench-game-{$i}"),
                    bggId: 13,
                    name: "Catan Edition {$i}",
                    yearPublished: 1995,
                    createdAt: new DateTime('now'),
                )
            );
        }
    }

    public function tearDown(): void
    {
        BenchHelper::clearRepositories();
    }

    #[Bench\Revs(100)]
    #[Bench\Iterations(5)]
    public function benchPingEndpoint(): void
    {
        $this->apiAction->handle($this->pingRequest);
    }

    #[Bench\Revs(50)]
    #[Bench\Iterations(5)]
    public function benchSearchEndpoint(): void
    {
        $this->apiAction->handle($this->searchRequest);
    }
}
