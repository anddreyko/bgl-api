<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Http\Middlewares;

use App\Core\Http\Middlewares\TrimMiddleware;
use Codeception\Test\Unit;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\ServerRequestFactory;

/**
 * @covers \App\Core\Http\Middlewares\TrimMiddleware
 */
final class TrimMiddlewareTest extends Unit
{
    public function testSuccess(): void
    {
        $middleware = new TrimMiddleware();

        $request = (new ServerRequestFactory())->createServerRequest('POST', 'http://app.test')
            ->withParsedBody([
                'null' => null,
                'space' => '  ',
                'string' => 'String ',
                'number' => 42,
                'array' => [
                    'null' => null,
                    'space' => '   ',
                    'string' => ' String',
                    'number' => 42,
                ],
            ]);

        $handler = $this->makeEmpty(
            RequestHandlerInterface::class,
            [
                'handle' => static function (ServerRequestInterface $request) {
                    self::assertEquals(
                        [
                            'null' => null,
                            'space' => '',
                            'string' => 'String',
                            'number' => 42,
                            'array' => [
                                'null' => null,
                                'space' => '',
                                'string' => 'String',
                                'number' => 42,
                            ],
                        ],
                        $request->getParsedBody()
                    );

                    return (new ResponseFactory())->createResponse();
                },
            ]
        );

        $middleware->process($request, $handler);
    }
}
