<?php

declare(strict_types=1);

namespace Bgl\Tests\Benchmark\Http;

use Bgl\Presentation\Api\CompiledRouteMap;
use Bgl\Tests\Benchmark\BenchHelper;
use PhpBench\Attributes as Bench;

#[Bench\BeforeMethods('setUp')]
final class HttpRoutingBench
{
    private CompiledRouteMap $routeMap;

    public function setUp(): void
    {
        /** @var array{paths: array<string, array<string, mixed>>} $config */
        $config = BenchHelper::container()->get('openapi');
        $this->routeMap = new CompiledRouteMap($config['paths'] ?? []);
    }

    #[Bench\Revs(1000)]
    #[Bench\Iterations(5)]
    public function benchStaticRouteMatch(): void
    {
        $this->routeMap->match('GET', '/ping');
    }

    #[Bench\Revs(1000)]
    #[Bench\Iterations(5)]
    public function benchDynamicRouteMatch(): void
    {
        $this->routeMap->match('GET', '/v1/games/some-uuid-here');
    }

    #[Bench\Revs(1000)]
    #[Bench\Iterations(5)]
    public function benchRouteNotFound(): void
    {
        $this->routeMap->match('GET', '/v1/nonexistent/path');
    }
}
