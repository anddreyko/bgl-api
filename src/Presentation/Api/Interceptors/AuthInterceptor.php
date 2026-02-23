<?php

declare(strict_types=1);

namespace Bgl\Presentation\Api\Interceptors;

use Bgl\Core\Auth\AuthenticationException;
use Bgl\Core\Security\TokenGenerator;
use Bgl\Domain\Auth\Entities\Users;
use Psr\Http\Message\ServerRequestInterface;

final readonly class AuthInterceptor implements Interceptor
{
    public function __construct(
        private TokenGenerator $tokenGenerator,
        private Users $users,
    ) {
    }

    #[\Override]
    public function process(ServerRequestInterface $request): ServerRequestInterface
    {
        $header = $request->getHeaderLine('Authorization');
        if (!str_starts_with($header, 'Bearer ')) {
            throw new AuthenticationException('Unauthorized');
        }

        $token = substr($header, 7);

        try {
            $payload = $this->tokenGenerator->verify($token);
        } catch (\RuntimeException $e) {
            throw new AuthenticationException($e->getMessage(), (int) $e->getCode(), $e);
        }

        if (!isset($payload['userId']) || !is_string($payload['userId'])) {
            throw new AuthenticationException('Unauthorized');
        }

        if (isset($payload['type']) && $payload['type'] !== 'access') {
            throw new AuthenticationException('Unauthorized');
        }

        $user = $this->users->find($payload['userId']);
        if ($user === null) {
            throw new AuthenticationException('User not found');
        }

        $payloadVersion = isset($payload['tokenVersion']) && is_int($payload['tokenVersion'])
            ? $payload['tokenVersion']
            : 0;

        if ($payloadVersion !== $user->getTokenVersion()) {
            throw new AuthenticationException('Token has been revoked');
        }

        return $request->withAttribute('auth.userId', $payload['userId']);
    }
}
