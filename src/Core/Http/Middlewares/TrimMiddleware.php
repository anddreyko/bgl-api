<?php

declare(strict_types=1);

namespace App\Core\Http\Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * @see \Tests\Unit\Core\Http\Middlewares\TrimMiddlewareTest
 */
final class TrimMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $handler->handle($request->withParsedBody($this->handleBody($request->getParsedBody())));
    }

    /**
     * @param array<array-key, mixed>|null|object $body
     *
     * @return array<array-key, mixed>|null|object
     */
    private function handleBody(mixed $body): mixed
    {
        if (is_array($body)) {
            $body = array_map(
                fn($item) => match (true) {
                    is_string($item) => trim($item),
                    is_array($item) => $this->handleBody($item),
                    default => $item
                },
                $body
            );
        }

        return $body;
    }
}
