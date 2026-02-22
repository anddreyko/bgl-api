<?php

declare(strict_types=1);

namespace Bgl\Presentation\Api\Interceptors;

use Bgl\Core\Security\TokenGenerator;
use Psr\Http\Message\ServerRequestInterface;

final readonly class AuthInterceptor implements Interceptor
{
    public function __construct(
        private TokenGenerator $tokenGenerator,
    ) {
    }

    #[\Override]
    public function process(ServerRequestInterface $request): ServerRequestInterface
    {
        $header = $request->getHeaderLine('Authorization');
        if (!str_starts_with($header, 'Bearer ')) {
            throw new \DomainException('Unauthorized');
        }

        $token = substr($header, 7);
        $payload = $this->tokenGenerator->verify($token);

        if (!isset($payload['userId']) || !is_string($payload['userId'])) {
            throw new \DomainException('Unauthorized');
        }

        if (isset($payload['type']) && $payload['type'] !== 'access') {
            throw new \DomainException('Unauthorized');
        }

        return $request->withAttribute('auth.userId', $payload['userId']);
    }
}
