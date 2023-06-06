<?php

declare(strict_types=1);

namespace App\Core\Http\Middlewares;

use App\Core\Http\Services\AcceptLanguageService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * @see \Tests\Unit\Core\Http\Middlewares\LocaleMiddlewareTest
 */
final readonly class LocaleMiddleware implements MiddlewareInterface
{
    public function __construct(private AcceptLanguageService $language)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $handler->handle($request->withHeader('Accept-Language', $this->language->handle()));
    }
}
