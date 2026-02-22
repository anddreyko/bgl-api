<?php

declare(strict_types=1);

namespace Bgl\Tests\Functional;

use Bgl\Core\Http\RequestValidator;
use Bgl\Core\Http\SchemaMapper;
use Bgl\Core\Messages\Dispatcher;
use Bgl\Core\Serialization\Serializer;
use Bgl\Presentation\Api\ApiAction;
use Bgl\Presentation\Api\InterceptorPipeline;
use Bgl\Presentation\Api\RouteMap;
use Bgl\Tests\Support\FunctionalTester;
use Bgl\Tests\Support\Messages\Ping;
use Codeception\Attribute\Group;
use Codeception\Stub;
use DI\Container;
use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Psr7\ServerRequest;

/**
 * @covers \Bgl\Presentation\Api\ApiAction
 */
#[Group('presentation', 'api-action')]
final class ApiActionCest
{
    public function testSuccessfulDispatchReturnsJson200(FunctionalTester $i): void
    {
        $routeMap = new RouteMap([
            '/ping' => [
                'get' => [
                    'x-message' => Ping::class,
                ],
            ],
        ]);

        $pipeline = new InterceptorPipeline(new Container());

        $schemaMapper = Stub::makeEmpty(SchemaMapper::class, [
            'map' => static fn(): array => ['text' => 'hello'],
        ]);

        $dispatcher = Stub::makeEmpty(Dispatcher::class, [
            'dispatch' => static fn(): string => 'pong',
        ]);

        $serializer = Stub::makeEmpty(Serializer::class);

        $requestValidator = Stub::makeEmpty(RequestValidator::class, [
            'validate' => static fn(): array => [],
        ]);

        $action = new ApiAction(
            routeMap: $routeMap,
            interceptorPipeline: $pipeline,
            requestValidator: $requestValidator,
            schemaMapper: $schemaMapper,
            dispatcher: $dispatcher,
            serializer: $serializer,
            responseFactory: new HttpFactory(),
            debugMode: false,
        );

        $request = new ServerRequest('GET', '/ping');
        $response = $action->handle($request);

        $i->assertSame(200, $response->getStatusCode());
        $i->assertSame('application/json', $response->getHeaderLine('Content-Type'));

        /** @var array{code: int, data: mixed} $body */
        $body = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        $i->assertSame(0, $body['code']);
        $i->assertSame('pong', $body['data']);
    }

    public function testRouteNotFoundReturns404(FunctionalTester $i): void
    {
        $routeMap = new RouteMap([]);

        $pipeline = new InterceptorPipeline(new Container());
        $schemaMapper = Stub::makeEmpty(SchemaMapper::class);
        $dispatcher = Stub::makeEmpty(Dispatcher::class);
        $serializer = Stub::makeEmpty(Serializer::class);

        $requestValidator = Stub::makeEmpty(RequestValidator::class, [
            'validate' => static fn(): array => [],
        ]);

        $action = new ApiAction(
            routeMap: $routeMap,
            interceptorPipeline: $pipeline,
            requestValidator: $requestValidator,
            schemaMapper: $schemaMapper,
            dispatcher: $dispatcher,
            serializer: $serializer,
            responseFactory: new HttpFactory(),
            debugMode: false,
        );

        $request = new ServerRequest('GET', '/nonexistent');
        $response = $action->handle($request);

        $i->assertSame(404, $response->getStatusCode());
        $i->assertSame('application/json', $response->getHeaderLine('Content-Type'));

        /** @var array{code: int, message: string} $body */
        $body = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        $i->assertSame(1, $body['code']);
        $i->assertSame('Not Found', $body['message']);
    }

    public function testHandlerExceptionReturns500(FunctionalTester $i): void
    {
        $routeMap = new RouteMap([
            '/ping' => [
                'get' => [
                    'x-message' => Ping::class,
                ],
            ],
        ]);

        $pipeline = new InterceptorPipeline(new Container());

        $schemaMapper = Stub::makeEmpty(SchemaMapper::class, [
            'map' => static fn(): array => ['text' => 'hello'],
        ]);

        $dispatcher = Stub::makeEmpty(Dispatcher::class, [
            'dispatch' => static function (): never {
                throw new \RuntimeException('Something went wrong');
            },
        ]);

        $serializer = Stub::makeEmpty(Serializer::class);

        $requestValidator = Stub::makeEmpty(RequestValidator::class, [
            'validate' => static fn(): array => [],
        ]);

        $action = new ApiAction(
            routeMap: $routeMap,
            interceptorPipeline: $pipeline,
            requestValidator: $requestValidator,
            schemaMapper: $schemaMapper,
            dispatcher: $dispatcher,
            serializer: $serializer,
            responseFactory: new HttpFactory(),
            debugMode: false,
        );

        $request = new ServerRequest('GET', '/ping');
        $response = $action->handle($request);

        $i->assertSame(500, $response->getStatusCode());
        $i->assertSame('application/json', $response->getHeaderLine('Content-Type'));

        /** @var array{code: int, message: string} $body */
        $body = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        $i->assertSame(1, $body['code']);
        $i->assertSame('Internal Server Error', $body['message']);
    }
}
