<?php

declare(strict_types=1);

namespace Bgl\Tests\Unit\Presentation\Api;

use Bgl\Presentation\Api\InterceptorPipeline;
use Bgl\Presentation\Api\Interceptors\Interceptor;
use Bgl\Tests\Support\UnitTester;
use Codeception\Attribute\Group;
use DI\Container;
use Psr\Http\Message\ServerRequestInterface;
use GuzzleHttp\Psr7\ServerRequest;

/**
 * @covers \Bgl\Presentation\Api\InterceptorPipeline
 */
#[Group('presentation', 'interceptor-pipeline')]
final class InterceptorPipelineCest
{
    public function testEmptyPipeline(UnitTester $i): void
    {
        $pipeline = new InterceptorPipeline(new Container());
        $request = new ServerRequest('GET', '/ping');

        $result = $pipeline->process($request, []);

        $i->assertSame($request, $result);
    }

    public function testSingleInterceptor(UnitTester $i): void
    {
        $interceptor = new class () implements Interceptor {
            #[\Override]
            public function process(ServerRequestInterface $request): ServerRequestInterface
            {
                return $request->withAttribute('intercepted', true);
            }
        };

        $container = new Container([
            $interceptor::class => $interceptor,
        ]);

        $pipeline = new InterceptorPipeline($container);
        $request = new ServerRequest('GET', '/ping');

        $result = $pipeline->process($request, [$interceptor::class]);

        $i->assertSame(true, $result->getAttribute('intercepted'));
    }

    public function testMultipleInterceptorsExecuteInOrder(UnitTester $i): void
    {
        $first = new class () implements Interceptor {
            #[\Override]
            public function process(ServerRequestInterface $request): ServerRequestInterface
            {
                /** @var list<string> $log */
                $log = $request->getAttribute('log', []);
                $log[] = 'first';

                return $request->withAttribute('log', $log);
            }
        };

        $second = new class () implements Interceptor {
            #[\Override]
            public function process(ServerRequestInterface $request): ServerRequestInterface
            {
                /** @var list<string> $log */
                $log = $request->getAttribute('log', []);
                $log[] = 'second';

                return $request->withAttribute('log', $log);
            }
        };

        $container = new Container([
            $first::class => $first,
            $second::class => $second,
        ]);

        $pipeline = new InterceptorPipeline($container);
        $request = new ServerRequest('GET', '/ping');

        $result = $pipeline->process($request, [$first::class, $second::class]);

        $i->assertSame(['first', 'second'], $result->getAttribute('log'));
    }
}
