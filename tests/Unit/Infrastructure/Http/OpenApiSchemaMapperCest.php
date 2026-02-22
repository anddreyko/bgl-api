<?php

declare(strict_types=1);

namespace Bgl\Tests\Unit\Infrastructure\Http;

use Bgl\Infrastructure\Http\OpenApiSchemaMapper;
use Bgl\Tests\Support\UnitTester;
use Codeception\Attribute\Group;
use GuzzleHttp\Psr7\ServerRequest;

/**
 * @covers \Bgl\Infrastructure\Http\OpenApiSchemaMapper
 */
#[Group('infrastructure', 'schema-mapper')]
final class OpenApiSchemaMapperCest
{
    private OpenApiSchemaMapper $mapper;

    public function _before(): void
    {
        $this->mapper = new OpenApiSchemaMapper();
    }

    public function testMapBodyParam(UnitTester $i): void
    {
        $request = new ServerRequest('POST', '/v1/users');
        $request = $request->withParsedBody(['email' => 'test@example.com']);

        $schema = [
            'email' => ['type' => 'string', 'x-target' => 'email'],
        ];

        $result = $this->mapper->map($request, $schema);

        $i->assertSame(['email' => 'test@example.com'], $result);
    }

    public function testMapQueryParam(UnitTester $i): void
    {
        $request = new ServerRequest('GET', '/v1/users?page=2');
        $request = $request->withQueryParams(['page' => '2']);

        $schema = [
            'page' => ['type' => 'integer', 'x-target' => 'page|int'],
        ];

        $result = $this->mapper->map($request, $schema);

        $i->assertSame(['page' => 2], $result);
    }

    public function testMapPathParam(UnitTester $i): void
    {
        $request = new ServerRequest('GET', '/v1/auth/confirm/abc123');

        $schema = [
            'token' => ['type' => 'string', 'x-target' => 'token'],
        ];

        $result = $this->mapper->map($request, $schema, ['token' => 'abc123']);

        $i->assertSame(['token' => 'abc123'], $result);
    }

    public function testCastInt(UnitTester $i): void
    {
        $request = new ServerRequest('GET', '/test');
        $request = $request->withQueryParams(['limit' => '10']);

        $schema = [
            'limit' => ['type' => 'integer', 'x-target' => 'limit|int'],
        ];

        $result = $this->mapper->map($request, $schema);

        $i->assertSame(10, $result['limit']);
    }

    public function testCastBool(UnitTester $i): void
    {
        $request = new ServerRequest('GET', '/test');
        $request = $request->withQueryParams(['active' => '1']);

        $schema = [
            'active' => ['type' => 'boolean', 'x-target' => 'active|bool'],
        ];

        $result = $this->mapper->map($request, $schema);

        $i->assertSame(true, $result['active']);
    }

    public function testCastFloat(UnitTester $i): void
    {
        $request = new ServerRequest('GET', '/test');
        $request = $request->withQueryParams(['score' => '3.14']);

        $schema = [
            'score' => ['type' => 'number', 'x-target' => 'score|float'],
        ];

        $result = $this->mapper->map($request, $schema);

        $i->assertSame(3.14, $result['score']);
    }

    public function testCastDatetime(UnitTester $i): void
    {
        $request = new ServerRequest('POST', '/test');
        $request = $request->withParsedBody(['created_at' => '2025-01-01T12:00:00+00:00']);

        $schema = [
            'created_at' => ['type' => 'string', 'x-target' => 'createdAt|datetime'],
        ];

        $result = $this->mapper->map($request, $schema);

        $i->assertInstanceOf(\DateTimeImmutable::class, $result['createdAt']);
        $i->assertSame('2025-01-01', $result['createdAt']->format('Y-m-d'));
    }

    public function testEmptySchema(UnitTester $i): void
    {
        $request = new ServerRequest('GET', '/ping');

        $result = $this->mapper->map($request, []);

        $i->assertSame([], $result);
    }

    public function testPathParamOverridesBody(UnitTester $i): void
    {
        $request = new ServerRequest('POST', '/v1/items/123');
        $request = $request->withParsedBody(['id' => '456']);

        $schema = [
            'id' => ['type' => 'string', 'x-target' => 'id'],
        ];

        $result = $this->mapper->map($request, $schema, ['id' => '123']);

        $i->assertSame(['id' => '123'], $result);
    }

    public function testSkipNullValues(UnitTester $i): void
    {
        $request = new ServerRequest('POST', '/test');

        $schema = [
            'email' => ['type' => 'string', 'x-target' => 'email'],
            'name' => ['type' => 'string', 'x-target' => 'name'],
        ];

        $result = $this->mapper->map($request, $schema);

        $i->assertSame([], $result);
    }

    public function testXSourceAttributeResolution(UnitTester $i): void
    {
        $request = new ServerRequest('POST', '/v1/plays');
        $request = $request
            ->withAttribute('auth.userId', 'user-abc-123')
            ->withParsedBody(['name' => 'Game Night']);

        $schema = [
            'userId' => ['type' => 'string', 'x-target' => 'userId', 'x-source' => 'attribute:auth.userId'],
            'name' => ['type' => 'string', 'x-target' => 'name'],
        ];

        $result = $this->mapper->map($request, $schema);

        $i->assertSame('user-abc-123', $result['userId']);
        $i->assertSame('Game Night', $result['name']);
    }

    public function testXSourceAttributeWithTargetRename(UnitTester $i): void
    {
        $request = new ServerRequest('POST', '/v1/plays');
        $request = $request->withAttribute('auth.userId', 'user-xyz');

        $schema = [
            'author' => ['type' => 'string', 'x-target' => 'authorId', 'x-source' => 'attribute:auth.userId'],
        ];

        $result = $this->mapper->map($request, $schema);

        $i->assertSame('user-xyz', $result['authorId']);
    }

    public function testXSourceAttributeNullFallsToResolveValue(UnitTester $i): void
    {
        $request = new ServerRequest('POST', '/v1/plays');
        $request = $request->withParsedBody(['userId' => 'body-user']);

        $schema = [
            'userId' => ['type' => 'string', 'x-target' => 'userId', 'x-source' => 'attribute:auth.userId'],
        ];

        $result = $this->mapper->map($request, $schema);

        $i->assertSame('body-user', $result['userId']);
    }
}
