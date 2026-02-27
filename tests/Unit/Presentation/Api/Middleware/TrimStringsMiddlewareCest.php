<?php

declare(strict_types=1);

namespace Bgl\Tests\Unit\Presentation\Api\Middleware;

use Bgl\Presentation\Api\Middleware\TrimStringsMiddleware;
use Bgl\Tests\Support\UnitTester;
use Codeception\Attribute\Group;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * @covers \Bgl\Presentation\Api\Middleware\TrimStringsMiddleware
 */
#[Group('presentation', 'middleware', 'trim')]
final class TrimStringsMiddlewareCest
{
    public function testTrimsWhitespaceFromParsedBodyStrings(UnitTester $i): void
    {
        $request = new ServerRequest('POST', '/test')
            ->withParsedBody(['name' => '  hello  ', 'title' => "\tfoo\n"]);

        $captured = $this->processAndCapture($request);

        /** @var array<string, string> $body */
        $body = $captured->getParsedBody();
        $i->assertSame('hello', $body['name']);
        $i->assertSame('foo', $body['title']);
    }

    public function testTrimsWhitespaceFromQueryParams(UnitTester $i): void
    {
        $request = new ServerRequest('GET', '/test')
            ->withQueryParams(['search' => '  board game  ', 'tag' => ' strategy ']);

        $captured = $this->processAndCapture($request);

        $query = $captured->getQueryParams();
        $i->assertSame('board game', $query['search']);
        $i->assertSame('strategy', $query['tag']);
    }

    public function testNonStringValuesInBodyAreNotModified(UnitTester $i): void
    {
        $request = new ServerRequest('POST', '/test')
            ->withParsedBody(['count' => 42, 'active' => true, 'meta' => null]);

        $captured = $this->processAndCapture($request);

        /** @var array<string, mixed> $body */
        $body = $captured->getParsedBody();
        $i->assertSame(42, $body['count']);
        $i->assertSame(true, $body['active']);
        $i->assertNull($body['meta']);
    }

    public function testNestedArraysInBodyAreTrimmedRecursively(UnitTester $i): void
    {
        $request = new ServerRequest('POST', '/test')
            ->withParsedBody([
                'player' => [
                    'name' => '  Alice  ',
                    'tags' => [' tag1 ', ' tag2 '],
                ],
            ]);

        $captured = $this->processAndCapture($request);

        /** @var array{player: array{name: string, tags: list<string>}} $body */
        $body = $captured->getParsedBody();
        $i->assertSame('Alice', $body['player']['name']);
        $i->assertSame(['tag1', 'tag2'], $body['player']['tags']);
    }

    public function testNullBodyDoesNotCauseErrors(UnitTester $i): void
    {
        $request = new ServerRequest('GET', '/test');

        $captured = $this->processAndCapture($request);

        $i->assertNull($captured->getParsedBody());
    }

    private function processAndCapture(ServerRequestInterface $request): ServerRequestInterface
    {
        /** @var ServerRequestInterface|null $captured */
        $captured = null;

        $handler = new class ($captured) implements RequestHandlerInterface {
            public function __construct(private ServerRequestInterface|null &$captured)
            {
            }

            #[\Override]
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $this->captured = $request;

                return new Response();
            }
        };

        new TrimStringsMiddleware()->process($request, $handler);

        assert($captured instanceof ServerRequestInterface);

        return $captured;
    }
}
