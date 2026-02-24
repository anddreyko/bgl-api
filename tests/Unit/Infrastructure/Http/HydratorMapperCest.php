<?php

declare(strict_types=1);

namespace Bgl\Tests\Unit\Infrastructure\Http;

use Bgl\Infrastructure\Http\HydratorMapper;
use Bgl\Tests\Support\UnitTester;
use Codeception\Attribute\Group;
use GuzzleHttp\Psr7\ServerRequest;

/**
 * @covers \Bgl\Infrastructure\Http\HydratorMapper
 */
#[Group('infrastructure', 'hydrator-mapper')]
final class HydratorMapperCest
{
    private HydratorMapper $mapper;

    public function _before(): void
    {
        $this->mapper = new HydratorMapper();
    }

    public function testBodyParamsPassedThrough(UnitTester $i): void
    {
        $request = new ServerRequest('POST', '/v1/test');
        $request = $request->withParsedBody(['name' => 'Game Night', 'score' => 42]);

        $result = $this->mapper->map($request);

        $i->assertSame('Game Night', $result['name']);
        $i->assertSame(42, $result['score']);
    }

    public function testPathParamsRenamed(UnitTester $i): void
    {
        $request = new ServerRequest('PATCH', '/v1/plays/sessions/abc');

        $result = $this->mapper->map(
            $request,
            pathParams: ['id' => 'abc'],
            paramMap: ['id' => 'sessionId'],
        );

        $i->assertSame('abc', $result['sessionId']);
        $i->assertArrayNotHasKey('id', $result);
    }

    public function testPathParamsWithoutMapKeepOriginalName(UnitTester $i): void
    {
        $request = new ServerRequest('GET', '/v1/auth/confirm/tok123');

        $result = $this->mapper->map(
            $request,
            pathParams: ['token' => 'tok123'],
        );

        $i->assertSame('tok123', $result['token']);
    }

    public function testAuthParamsInjected(UnitTester $i): void
    {
        $request = new ServerRequest('POST', '/v1/plays/sessions');
        $request = $request->withAttribute('auth.userId', 'user-abc-123');

        $result = $this->mapper->map(
            $request,
            authParams: ['userId'],
        );

        $i->assertSame('user-abc-123', $result['userId']);
    }

    public function testAuthParamsNullNotIncluded(UnitTester $i): void
    {
        $request = new ServerRequest('POST', '/v1/plays/sessions');

        $result = $this->mapper->map(
            $request,
            authParams: ['userId'],
        );

        $i->assertArrayNotHasKey('userId', $result);
    }

    public function testQueryParamsIncluded(UnitTester $i): void
    {
        $request = new ServerRequest('GET', '/v1/users?page=2&limit=10');
        $request = $request->withQueryParams(['page' => '2', 'limit' => '10']);

        $result = $this->mapper->map($request);

        $i->assertSame('2', $result['page']);
        $i->assertSame('10', $result['limit']);
    }

    public function testAllSourcesCombined(UnitTester $i): void
    {
        $request = new ServerRequest('PATCH', '/v1/plays/sessions/sess-123');
        $request = $request
            ->withParsedBody(['finishedAt' => '2025-01-01T12:00:00Z'])
            ->withAttribute('auth.userId', 'user-xyz');

        $result = $this->mapper->map(
            $request,
            pathParams: ['id' => 'sess-123'],
            authParams: ['userId'],
            paramMap: ['id' => 'sessionId'],
        );

        $i->assertSame('sess-123', $result['sessionId']);
        $i->assertSame('user-xyz', $result['userId']);
        $i->assertSame('2025-01-01T12:00:00Z', $result['finishedAt']);
    }

    public function testEmptyRequestReturnsEmpty(UnitTester $i): void
    {
        $request = new ServerRequest('GET', '/ping');

        $result = $this->mapper->map($request);

        $i->assertSame([], $result);
    }
}
