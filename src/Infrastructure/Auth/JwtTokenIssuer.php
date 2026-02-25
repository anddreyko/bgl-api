<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Auth;

use Bgl\Core\Auth\AuthenticationException;
use Bgl\Core\Auth\TokenIssuer;
use Bgl\Core\Auth\TokenPair;
use Bgl\Core\Security\Tokenizer;
use Bgl\Core\Security\TokenConfig;
use Bgl\Domain\Profile\Entities\Users;

/**
 * @see \Bgl\Tests\Unit\Infrastructure\Auth\JwtTokenIssuerCest
 */
final readonly class JwtTokenIssuer implements TokenIssuer
{
    public function __construct(
        private Tokenizer $tokenizer,
        private Users $users,
        private TokenConfig $tokenConfig,
    ) {
    }

    #[\Override]
    public function issue(string $userId): TokenPair
    {
        $user = $this->users->find($userId);
        if ($user === null) {
            throw new AuthenticationException('Unauthorized');
        }

        $accessToken = $this->tokenizer->generate(
            ['userId' => $userId, 'type' => 'access', 'tokenVersion' => $user->getTokenVersion()],
            $this->tokenConfig->accessTtl,
        );

        $refreshToken = $this->tokenizer->generate(
            ['userId' => $userId, 'type' => 'refresh', 'tokenVersion' => $user->getTokenVersion()],
            $this->tokenConfig->refreshTtl,
        );

        return new TokenPair(
            accessToken: $accessToken,
            refreshToken: $refreshToken,
            expiresIn: $this->tokenConfig->accessTtl,
        );
    }
}
