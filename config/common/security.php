<?php

declare(strict_types=1);

use Bgl\Core\Auth\Authenticator;
use Bgl\Core\Security\PasswordHasher;
use Bgl\Core\Security\Tokenizer;
use Bgl\Core\Security\TokenTtlConfig;
use Bgl\Infrastructure\Auth\JwtAuthenticator;
use Bgl\Infrastructure\Security\BcryptPasswordHasher;
use Bgl\Infrastructure\Security\JwtTokenizer;
use Psr\Clock\ClockInterface;

return [
    BcryptPasswordHasher::class => static fn(): BcryptPasswordHasher => new BcryptPasswordHasher(['cost' => 12]),
    PasswordHasher::class => DI\get(BcryptPasswordHasher::class),
    JwtTokenizer::class => static function (ClockInterface $clock): JwtTokenizer {
        $secret = (string) getenv('JWT_KEY');
        if ($secret === '') {
            throw new \RuntimeException('JWT_KEY environment variable is not set');
        }

        return new JwtTokenizer(secret: $secret, clock: $clock);
    },
    Tokenizer::class => DI\get(JwtTokenizer::class),
    TokenTtlConfig::class => static function (): TokenTtlConfig {
        $accessTtl = getenv('JWT_ACCESS_TTL');
        $refreshTtl = getenv('JWT_REFRESH_TTL');

        return new TokenTtlConfig(
            accessTtl: $accessTtl !== false && $accessTtl !== '' ? (int) $accessTtl : 7200,
            refreshTtl: $refreshTtl !== false && $refreshTtl !== '' ? (int) $refreshTtl : 2592000,
        );
    },
    Authenticator::class => DI\get(JwtAuthenticator::class),
];
