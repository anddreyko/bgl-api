<?php

declare(strict_types=1);

namespace Bgl\Tests\Unit\Presentation\Api;

use Bgl\Application\Handlers\Ping\Command;
use Bgl\Presentation\Api\CompiledRouteMap;
use Bgl\Tests\Support\UnitTester;
use Codeception\Attribute\Group;

/**
 * @covers \Bgl\Presentation\Api\CompiledRouteMap
 * @covers \Bgl\Presentation\Api\CompiledOperation
 * @covers \Bgl\Presentation\Api\MatchResult
 */
#[Group('presentation', 'compiled-route-map')]
final class CompiledRouteMapCest
{
    public function testStaticRouteMatch(UnitTester $i): void
    {
        $map = new CompiledRouteMap([
            '/ping' => [
                'get' => [
                    'x-message' => Command::class,
                ],
            ],
        ]);

        $result = $map->match('GET', '/ping');

        $i->assertNotNull($result);
        $i->assertSame(Command::class, $result->operation->messageClass);
        $i->assertSame([], $result->pathParams);
    }

    public function testDynamicRouteMatch(UnitTester $i): void
    {
        $map = new CompiledRouteMap([
            '/v1/user/{id}' => [
                'get' => [
                    'x-message' => Command::class,
                ],
            ],
        ]);

        $result = $map->match('GET', '/v1/user/abc');

        $i->assertNotNull($result);
        $i->assertSame(Command::class, $result->operation->messageClass);
        $i->assertSame(['id' => 'abc'], $result->pathParams);
    }

    public function testNoMatchReturnsNull(UnitTester $i): void
    {
        $map = new CompiledRouteMap([
            '/ping' => [
                'get' => [
                    'x-message' => Command::class,
                ],
            ],
        ]);

        $result = $map->match('GET', '/unknown');

        $i->assertNull($result);
    }

    public function testMethodMismatchReturnsNull(UnitTester $i): void
    {
        $map = new CompiledRouteMap([
            '/ping' => [
                'get' => [
                    'x-message' => Command::class,
                ],
            ],
        ]);

        $result = $map->match('POST', '/ping');

        $i->assertNull($result);
    }

    public function testAuthParamsExtracted(UnitTester $i): void
    {
        $map = new CompiledRouteMap([
            '/v1/plays/sessions' => [
                'post' => [
                    'x-message' => Command::class,
                    'x-auth' => ['userId'],
                ],
            ],
        ]);

        $result = $map->match('POST', '/v1/plays/sessions');

        $i->assertNotNull($result);
        $i->assertSame(['userId'], $result->operation->authParams);
    }

    public function testParamMapExtracted(UnitTester $i): void
    {
        $map = new CompiledRouteMap([
            '/v1/plays/sessions/{id}' => [
                'patch' => [
                    'x-message' => Command::class,
                    'x-map' => ['id' => 'sessionId'],
                ],
            ],
        ]);

        $result = $map->match('PATCH', '/v1/plays/sessions/abc');

        $i->assertNotNull($result);
        $i->assertSame(['id' => 'sessionId'], $result->operation->paramMap);
        $i->assertSame(['id' => 'abc'], $result->pathParams);
    }

    public function testInterceptorsExtracted(UnitTester $i): void
    {
        $map = new CompiledRouteMap([
            '/ping' => [
                'get' => [
                    'x-message' => Command::class,
                    'x-interceptors' => ['App\\AuthInterceptor'],
                ],
            ],
        ]);

        $result = $map->match('GET', '/ping');

        $i->assertNotNull($result);
        $i->assertSame(['App\\AuthInterceptor'], $result->operation->interceptors);
    }

    public function testCaseInsensitiveMethod(UnitTester $i): void
    {
        $map = new CompiledRouteMap([
            '/ping' => [
                'get' => [
                    'x-message' => Command::class,
                ],
            ],
        ]);

        $result = $map->match('get', '/ping');

        $i->assertNotNull($result);
        $i->assertSame(Command::class, $result->operation->messageClass);
    }

    public function testOpenApiSchemaPreserved(UnitTester $i): void
    {
        $operation = [
            'x-message' => Command::class,
            'requestBody' => [
                'required' => true,
                'content' => [
                    'application/json' => [
                        'schema' => [
                            'type' => 'object',
                            'properties' => [
                                'name' => ['type' => 'string'],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $map = new CompiledRouteMap([
            '/v1/test' => [
                'post' => $operation,
            ],
        ]);

        $result = $map->match('POST', '/v1/test');

        $i->assertNotNull($result);
        $i->assertSame($operation, $result->operation->openApiSchema);
    }
}
