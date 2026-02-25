<?php

declare(strict_types=1);

use Bgl\Core\Auth\Authenticator;
use Bgl\Core\Auth\PasskeyVerifier;
use Bgl\Core\Auth\TokenIssuer;
use Bgl\Core\Security\Hasher;
use Bgl\Core\Security\TokenConfig;
use Bgl\Core\Security\Tokenizer;
use Bgl\Domain\Profile\Entities\Users;
use Bgl\Infrastructure\Auth\JwtAuthenticator;
use Bgl\Infrastructure\Auth\JwtTokenIssuer;
use Bgl\Infrastructure\Auth\WebAuthnPasskeyVerifier;
use Bgl\Infrastructure\Security\BcryptHasher;
use Bgl\Infrastructure\Security\JwtTokenizer;
use Psr\Clock\ClockInterface;

return [
    BcryptHasher::class => static fn(): BcryptHasher => new BcryptHasher(['cost' => 12]),
    Hasher::class => static fn(BcryptHasher $h): Hasher => $h,
    JwtTokenizer::class => static function (ClockInterface $clock): JwtTokenizer {
        $secret = (string)getenv('JWT_KEY');
        if ($secret === '') {
            throw new \RuntimeException('JWT_KEY environment variable is not set');
        }

        return new JwtTokenizer(secret: $secret, clock: $clock);
    },
    Tokenizer::class => static fn(JwtTokenizer $t): Tokenizer => $t,
    TokenConfig::class => static function (): TokenConfig {
        $accessTtl = getenv('JWT_ACCESS_TTL');
        $refreshTtl = getenv('JWT_REFRESH_TTL');

        return new TokenConfig(
            accessTtl: $accessTtl !== false && $accessTtl !== '' ? (int)$accessTtl : 7200,
            refreshTtl: $refreshTtl !== false && $refreshTtl !== '' ? (int)$refreshTtl : 2592000,
        );
    },
    TokenIssuer::class => static fn(
        Tokenizer $t,
        Users $u,
        TokenConfig $c,
    ): TokenIssuer => new JwtTokenIssuer($t, $u, $c),
    Authenticator::class => static fn(
        Tokenizer $tokenizer,
        Users $users,
        Hasher $hasher,
        TokenIssuer $tokenIssuer,
    ): Authenticator => new JwtAuthenticator($tokenizer, $users, $hasher, $tokenIssuer),
    PasskeyVerifier::class => static function (): PasskeyVerifier {
        $rpId = (string)getenv('WEBAUTHN_RP_ID');
        $rpName = (string)getenv('WEBAUTHN_RP_NAME');

        if ($rpId === '' || $rpName === '') {
            throw new \RuntimeException('WEBAUTHN_RP_ID and WEBAUTHN_RP_NAME must be set');
        }

        return new WebAuthnPasskeyVerifier(rpId: $rpId, rpName: $rpName);
    },
];
