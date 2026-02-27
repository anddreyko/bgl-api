<?php

declare(strict_types=1);

namespace Bgl\Presentation\Api\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class TrimStringsMiddleware implements MiddlewareInterface
{
    #[\Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $body = $request->getParsedBody();
        if (is_array($body)) {
            array_walk_recursive($body, static function (mixed &$value): void {
                if (is_string($value)) {
                    $value = trim($value);
                }
            });
            $request = $request->withParsedBody($body);
        }

        $query = $request->getQueryParams();
        array_walk_recursive($query, static function (mixed &$value): void {
            if (is_string($value)) {
                $value = trim($value);
            }
        });
        $request = $request->withQueryParams($query);

        return $handler->handle($request);
    }
}
