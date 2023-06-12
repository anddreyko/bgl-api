<?php

declare(strict_types=1);

namespace App\Core\Tokens\Services;

use App\Auth\ValueObjects\WebToken;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * @see \Tests\Unit\Core\Tokens\Services\JsonWebTokenizerServiceTest
 */
final readonly class JsonWebTokenizerService
{
    public function __construct(private Key $key, private JWT $token = new JWT())
    {
    }

    /**
     * @param WebToken $token
     *
     * @return array<array-key, mixed>
     */
    public function decode(WebToken $token): array
    {
        return (array)$this->token->decode($token->getValue(), $this->key);
    }

    /**
     * @param array<array-key, mixed> $payload
     * @param string $expire
     * @param \DateTimeImmutable $issuedAt
     *
     * @return WebToken
     */
    public function encode(
        array $payload,
        string $expire = '+30 minutes',
        \DateTimeImmutable $issuedAt = new \DateTimeImmutable()
    ): WebToken {
        if (!isset($payload['iat'])) {
            $payload['iat'] = $issuedAt->getTimestamp();
        }
        if (!isset($payload['nbf'])) {
            $payload['nbf'] = $issuedAt->getTimestamp();
        }
        if (!isset($payload['exp'])) {
            $expireAt = $issuedAt->modify($expire);
            if ($expireAt) {
                $payload['exp'] = $expireAt->getTimestamp();
            }
        }

        return new WebToken($this->token->encode($payload, $this->key->getKeyMaterial(), $this->key->getAlgorithm()));
    }
}
