<?php

declare(strict_types=1);

namespace Bgl\Tests\Unit\Presentation\Api;

use Bgl\Application\Handlers\Ping\Command;
use Bgl\Presentation\Api\RouteMap;
use Bgl\Tests\Support\UnitTester;
use Codeception\Attribute\Group;

/**
 * @covers \Bgl\Presentation\Api\RouteMap
 * @covers \Bgl\Presentation\Api\MatchedOperation
 */
#[Group('presentation', 'route-map')]
final class RouteMapCest
{
    public function testMatchExactPath(UnitTester $i): void
    {
        $routeMap = new RouteMap([
            '/ping' => [
                'get' => [
                    'summary' => 'Health check',
                    'x-message' => Command::class,
                ],
            ],
        ]);

        $operation = $routeMap->match('GET', '/ping');

        $i->assertNotNull($operation);
        $i->assertSame(Command::class, $operation->messageClass);
    }

    public function testMatchWithPathParam(UnitTester $i): void
    {
        $routeMap = new RouteMap([
            '/v1/auth/confirm/{token}' => [
                'get' => [
                    'summary' => 'Confirm email',
                    'x-message' => Command::class,
                ],
            ],
        ]);

        $operation = $routeMap->match('GET', '/v1/auth/confirm/abc123');

        $i->assertNotNull($operation);
        $i->assertSame(['token' => 'abc123'], $operation->pathParams);
    }

    public function testNoMatchWrongMethod(UnitTester $i): void
    {
        $routeMap = new RouteMap([
            '/ping' => [
                'get' => [
                    'summary' => 'Health check',
                    'x-message' => Command::class,
                ],
            ],
        ]);

        $operation = $routeMap->match('POST', '/ping');

        $i->assertNull($operation);
    }

    public function testNoMatchUnknownPath(UnitTester $i): void
    {
        $routeMap = new RouteMap([
            '/ping' => [
                'get' => [
                    'summary' => 'Health check',
                    'x-message' => Command::class,
                ],
            ],
        ]);

        $operation = $routeMap->match('GET', '/unknown');

        $i->assertNull($operation);
    }

    public function testExtractMessageClass(UnitTester $i): void
    {
        $routeMap = new RouteMap([
            '/ping' => [
                'get' => [
                    'x-message' => Command::class,
                ],
            ],
        ]);

        $operation = $routeMap->match('GET', '/ping');

        $i->assertNotNull($operation);
        $i->assertSame(Command::class, $operation->messageClass);
    }

    public function testExtractInterceptors(UnitTester $i): void
    {
        $routeMap = new RouteMap([
            '/ping' => [
                'get' => [
                    'x-message' => Command::class,
                    'x-interceptors' => ['App\\Interceptor\\AuthInterceptor'],
                ],
            ],
        ]);

        $operation = $routeMap->match('GET', '/ping');

        $i->assertNotNull($operation);
        $i->assertSame(['App\\Interceptor\\AuthInterceptor'], $operation->interceptors);
    }

    public function testExtractSchema(UnitTester $i): void
    {
        $routeMap = new RouteMap([
            '/v1/users' => [
                'post' => [
                    'x-message' => Command::class,
                    'requestBody' => [
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'properties' => [
                                        'email' => ['type' => 'string', 'x-target' => 'email'],
                                        'password' => ['type' => 'string', 'x-target' => 'password'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $operation = $routeMap->match('POST', '/v1/users');

        $i->assertNotNull($operation);
        $i->assertArrayHasKey('email', $operation->schema);
        $i->assertArrayHasKey('password', $operation->schema);
    }
}
