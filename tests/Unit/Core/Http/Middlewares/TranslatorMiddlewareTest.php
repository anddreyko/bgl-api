<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Http\Middlewares;

use App\Application\Middleware\TranslatorMiddleware;
use App\Infrastructure\Localization\Translator;
use Codeception\Stub\Expected;
use Codeception\Test\Unit;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Factory\ServerRequestFactory;

/**
 * @covers \App\Application\Middleware\TranslatorMiddleware
 */
final class TranslatorMiddlewareTest extends Unit
{
    public function testSuccess(): void
    {
        $service = $this->makeEmpty(Translator::class, ['setLocale' => Expected::once()]);
        $middleware = new TranslatorMiddleware($service);

        $request = (new ServerRequestFactory())->createServerRequest('POST', 'http://app.test')
            ->withHeader('Accept-Language', 'fr');

        $handler = $this->makeEmpty(
            RequestHandlerInterface::class,
            ['handle' => fn() => $this->makeEmpty(ResponseInterface::class)]
        );

        $middleware->process($request, $handler);
    }

    public function testNotSetLocale(): void
    {
        $service = $this->makeEmpty(Translator::class, ['setLocale' => Expected::never()]);
        $middleware = new TranslatorMiddleware($service);

        $request = (new ServerRequestFactory())->createServerRequest('POST', 'http://app.test')
            ->withHeader('Accept-Language', '');

        $handler = $this->makeEmpty(
            RequestHandlerInterface::class,
            ['handle' => fn() => $this->makeEmpty(ResponseInterface::class)]
        );

        $middleware->process($request, $handler);
    }
}
