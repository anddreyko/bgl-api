<?php

declare(strict_types=1);

namespace App\Application\Middleware;

use App\Infrastructure\Localization\Translator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * @see \Tests\Unit\Core\Http\Middlewares\TranslatorMiddlewareTest
 */
final readonly class TranslatorMiddleware implements MiddlewareInterface
{
    public function __construct(private Translator $translator)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $locale = $request->getHeaderLine('Accept-Language');

        if (!empty($locale)) {
            $this->translator->setLocale($locale);
        }

        return $handler->handle($request);
    }
}
