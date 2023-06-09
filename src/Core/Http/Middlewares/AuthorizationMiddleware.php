<?php

declare(strict_types=1);

namespace App\Core\Http\Middlewares;

use App\Core\Http\Services\AuthorizationService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Exception\HttpUnauthorizedException;
use Slim\Routing\RouteContext;

/**
 * @see \Tests\Unit\Core\Http\Middlewares\AuthorizationMiddlewareTest
 */
final readonly class AuthorizationMiddleware implements MiddlewareInterface
{
    public const ATTRIBUTE_ACCESSED = 'accessed';
    public const ATTRIBUTE_IDENTITY = 'identity';

    public function __construct(private AuthorizationService $authorizationService)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!RouteContext::fromRequest($request)->getRoute()?->getArgument(self::ATTRIBUTE_ACCESSED)) {
            if (preg_match('/.*Bearer(.+)/i', $request->getHeaderLine('Authorization'), $result) === false) {
                throw new HttpUnauthorizedException($request);
            }

            if (!isset($result[1])) {
                throw new HttpUnauthorizedException($request);
            }

            $request = $request->withAttribute(
                self::ATTRIBUTE_IDENTITY,
                $this->authorizationService->handle(trim($result[1]))
            );
        }

        return $handler->handle($request);
    }
}
