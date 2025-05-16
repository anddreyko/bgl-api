<?php

declare(strict_types=1);

namespace App\Application\Middleware;

use App\Infrastructure\Http\LanguageAcceptor;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * @see \Tests\Unit\Core\Http\Middlewares\LocaleMiddlewareTest
 */
final readonly class LocaleMiddleware implements MiddlewareInterface
{
    public function __construct(private LanguageAcceptor $language)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $handler->handle($request->withHeader('Accept-Language', $this->language->handle()));
    }
}
