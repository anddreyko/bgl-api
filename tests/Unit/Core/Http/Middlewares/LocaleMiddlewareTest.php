<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Http\Middlewares;

use App\Core\Http\Middlewares\LocaleMiddleware;
use App\Core\Http\Services\AcceptLanguageService;
use Codeception\Test\Unit;
use Kudashevs\AcceptLanguage\AcceptLanguage;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\ServerRequestFactory;

/**
 * @covers \App\Core\Http\Middlewares\LocaleMiddleware
 */
final class LocaleMiddlewareTest extends Unit
{
    protected function tearDown(): void
    {
        parent::tearDown();

        @$_SERVER['HTTP_ACCEPT_LANGUAGE'] = null;
    }

    public function testSuccess(): void
    {
        @$_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en';
        $service = new AcceptLanguageService(new AcceptLanguage());
        $middleware = new LocaleMiddleware($service);

        $request = (new ServerRequestFactory())->createServerRequest('POST', 'http://app.test');

        $handler = $this->makeEmpty(
            RequestHandlerInterface::class,
            [
                'handle' => function (ServerRequestInterface $request) {
                    self::assertEquals('en', $request->getHeaderLine('Accept-Language'));

                    return (new ResponseFactory())->createResponse();
                },
            ]
        );

        $middleware->process($request, $handler);
    }

    public function testNotExistHeader(): void
    {
        @$_SERVER['HTTP_ACCEPT_LANGUAGE'] = null;
        $service = new AcceptLanguageService(new AcceptLanguage());
        $middleware = new LocaleMiddleware($service);

        $request = (new ServerRequestFactory())->createServerRequest('POST', 'http://app.test');

        $handler = $this->makeEmpty(
            RequestHandlerInterface::class,
            [
                'handle' => function (ServerRequestInterface $request) {
                    self::assertEquals(['en'], $request->getHeader('Accept-Language'));

                    return (new ResponseFactory())->createResponse();
                },
            ]
        );

        $middleware->process($request, $handler);
    }

    public function testOtherHeader(): void
    {
        @$_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'es';
        $service = new AcceptLanguageService(new AcceptLanguage(['accepted_languages' => ['en']]));
        $middleware = new LocaleMiddleware($service);

        $request = (new ServerRequestFactory())->createServerRequest('POST', 'http://app.test');

        $handler = $this->makeEmpty(
            RequestHandlerInterface::class,
            [
                'handle' => function (ServerRequestInterface $request) {
                    self::assertEquals(['en'], $request->getHeader('Accept-Language'));

                    return (new ResponseFactory())->createResponse();
                },
            ]
        );

        $middleware->process($request, $handler);
    }
}
