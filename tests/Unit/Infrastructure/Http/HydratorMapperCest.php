<?php

declare(strict_types=1);

namespace Bgl\Tests\Unit\Infrastructure\Http;

use Bgl\Core\Http\AuthParams;
use Bgl\Core\Http\ParamMap;
use Bgl\Core\Http\ParameterConflictException;
use Bgl\Core\Http\PathParams;
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

        $i->assertSame('Game Night', $result->get('name'));
        $i->assertSame(42, $result->get('score'));
    }

    public function testPathParamsRenamed(UnitTester $i): void
    {
        $request = new ServerRequest('PATCH', '/v1/plays/sessions/abc');

        $result = $this->mapper->map(
            $request,
            pathParams: new PathParams(['id' => 'abc']),
            paramMap: new ParamMap(['id' => 'sessionId']),
        );

        $i->assertSame('abc', $result->get('sessionId'));
        $i->assertFalse($result->has('id'));
    }

    public function testPathParamsWithoutMapKeepOriginalName(UnitTester $i): void
    {
        $request = new ServerRequest('GET', '/v1/auth/confirm/tok123');

        $result = $this->mapper->map(
            $request,
            pathParams: new PathParams(['token' => 'tok123']),
        );

        $i->assertSame('tok123', $result->get('token'));
    }

    public function testAuthParamsInjected(UnitTester $i): void
    {
        $request = new ServerRequest('POST', '/v1/plays/sessions');
        $request = $request->withAttribute('auth.userId', 'user-abc-123');

        $result = $this->mapper->map(
            $request,
            authParams: new AuthParams(['userId']),
        );

        $i->assertSame('user-abc-123', $result->get('userId'));
    }

    public function testAuthParamsNullNotIncluded(UnitTester $i): void
    {
        $request = new ServerRequest('POST', '/v1/plays/sessions');

        $result = $this->mapper->map(
            $request,
            authParams: new AuthParams(['userId']),
        );

        $i->assertFalse($result->has('userId'));
    }

    public function testQueryParamsIncluded(UnitTester $i): void
    {
        $request = new ServerRequest('GET', '/v1/users?page=2&limit=10');
        $request = $request->withQueryParams(['page' => '2', 'limit' => '10']);

        $result = $this->mapper->map($request);

        $i->assertSame('2', $result->get('page'));
        $i->assertSame('10', $result->get('limit'));
    }

    public function testAllSourcesCombined(UnitTester $i): void
    {
        $request = new ServerRequest('PATCH', '/v1/plays/sessions/sess-123');
        $request = $request
            ->withParsedBody(['finishedAt' => '2025-01-01T12:00:00Z'])
            ->withAttribute('auth.userId', 'user-xyz');

        $result = $this->mapper->map(
            $request,
            pathParams: new PathParams(['id' => 'sess-123']),
            authParams: new AuthParams(['userId']),
            paramMap: new ParamMap(['id' => 'sessionId']),
        );

        $i->assertSame('sess-123', $result->get('sessionId'));
        $i->assertSame('user-xyz', $result->get('userId'));
        $i->assertSame('2025-01-01T12:00:00Z', $result->get('finishedAt'));
    }

    public function testEmptyRequestReturnsEmpty(UnitTester $i): void
    {
        $request = new ServerRequest('GET', '/ping');

        $result = $this->mapper->map($request);

        $i->assertSame([], $result->toArray());
    }

    public function testPathParamConflictWithBodyThrows(UnitTester $i): void
    {
        $request = new ServerRequest('POST', '/v1/test');
        $request = $request->withParsedBody(['id' => 'from-body']);

        $i->expectThrowable(ParameterConflictException::class, fn() => $this->mapper->map(
            $request,
            pathParams: new PathParams(['id' => 'from-path']),
        ));
    }

    public function testAuthParamConflictWithBodyThrows(UnitTester $i): void
    {
        $request = new ServerRequest('POST', '/v1/test');
        $request = $request
            ->withParsedBody(['userId' => 'from-body'])
            ->withAttribute('auth.userId', 'from-auth');

        $i->expectThrowable(ParameterConflictException::class, fn() => $this->mapper->map(
            $request,
            authParams: new AuthParams(['userId']),
        ));
    }
}
