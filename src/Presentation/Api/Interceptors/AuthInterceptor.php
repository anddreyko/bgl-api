<?php

declare(strict_types=1);

namespace Bgl\Presentation\Api\Interceptors;

use Bgl\Core\Auth\AuthenticationException;
use Bgl\Core\Auth\Authenticator;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @see \Bgl\Tests\Unit\Presentation\Api\Interceptors\AuthInterceptorCest
 */
final readonly class AuthInterceptor implements Interceptor
{
    public function __construct(
        private Authenticator $authenticator,
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
        $authPayload = $this->authenticator->verify($token);

        return $request->withAttribute('auth.userId', $authPayload->userId);
    }
}
