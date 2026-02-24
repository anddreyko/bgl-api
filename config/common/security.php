<?php

declare(strict_types=1);

use Bgl\Core\Auth\Authenticator;
use Bgl\Core\Security\PasswordHasher;
use Bgl\Core\Security\TokenGenerator;
use Bgl\Core\Security\TokenTtlConfig;
use Bgl\Infrastructure\Auth\JwtAuthenticator;
use Bgl\Infrastructure\Security\BcryptPasswordHasher;
use Bgl\Infrastructure\Security\JwtTokenGenerator;
use Psr\Clock\ClockInterface;

return [
    BcryptPasswordHasher::class => static fn(): BcryptPasswordHasher => new BcryptPasswordHasher(['cost' => 12]),
    PasswordHasher::class => DI\get(BcryptPasswordHasher::class),
    JwtTokenGenerator::class => static function (ClockInterface $clock): JwtTokenGenerator {
        $secret = (string) getenv('JWT_KEY');
        if ($secret === '') {
            throw new \RuntimeException('JWT_KEY environment variable is not set');
        }

        return new JwtTokenGenerator(secret: $secret, clock: $clock);
    },
    TokenGenerator::class => DI\get(JwtTokenGenerator::class),
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
