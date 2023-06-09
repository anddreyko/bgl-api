<?php

declare(strict_types=1);

namespace App\Core\Tokens\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * @see \Tests\Unit\Core\Tokens\Services\JsonWebTokenizerServiceTest
 */
final readonly class JsonWebTokenizerService
{
    public function __construct(private JWT $token, private Key $key)
    {
    }

    /**
     * @param string $token
     *
     * @return array<array-key, mixed>
     */
    public function decode(string $token): array
    {
        return (array)$this->token->decode($token, $this->key);
    }

    /**
     * @param array<array-key, mixed> $payload
     * @param string $expire
     * @param \DateTimeImmutable $issuedAt
     *
     * @return string
     */
    public function encode(
        array $payload,
        string $expire = '+30 minutes',
        \DateTimeImmutable $issuedAt = new \DateTimeImmutable()
    ): string {
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

        return $this->token->encode($payload, $this->key->getKeyMaterial(), $this->key->getAlgorithm());
    }
}
