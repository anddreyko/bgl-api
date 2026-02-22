<?php

declare(strict_types=1);

use Bgl\Core\Security\PasswordHasher;
use Bgl\Core\Security\TokenGenerator;
use Bgl\Infrastructure\Security\BcryptPasswordHasher;
use Bgl\Infrastructure\Security\JwtTokenGenerator;
use Psr\Clock\ClockInterface;

return [
    BcryptPasswordHasher::class => static fn(): BcryptPasswordHasher => new BcryptPasswordHasher(['cost' => 12]),
    PasswordHasher::class => BcryptPasswordHasher::class,
    JwtTokenGenerator::class => static function (ClockInterface $clock): JwtTokenGenerator {
        $secret = (string) getenv('JWT_KEY');
        if ($secret === '') {
            throw new \RuntimeException('JWT_KEY environment variable is not set');
        }

        return new JwtTokenGenerator(secret: $secret, clock: $clock);
    },
    TokenGenerator::class => JwtTokenGenerator::class,
];
