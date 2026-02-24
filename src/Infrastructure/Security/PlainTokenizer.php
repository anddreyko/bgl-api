<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Security;

use Bgl\Core\Security\Tokenizer;
use Psr\Clock\ClockInterface;

/**
 * @see \Bgl\Tests\Unit\Infrastructure\Security\PlainTokenizerCest
 */
final readonly class PlainTokenizer implements Tokenizer
{
    public function __construct(
        private ClockInterface $clock,
    ) {
    }

    #[\Override]
    public function generate(array $payload, int $ttlSeconds): string
    {
        $data = [
            'payload' => $payload,
            'exp' => $this->clock->now()->getTimestamp() + $ttlSeconds,
        ];

        $json = json_encode($data, JSON_THROW_ON_ERROR);

        return base64_encode($json);
    }

    #[\Override]
    public function verify(string $token): array
    {
        try {
            $json = base64_decode($token, true);
            if ($json === false) {
                throw new \RuntimeException('Invalid token encoding');
            }

            /** @var array{payload: array<string, mixed>, exp: int} $data */
            $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new \RuntimeException('Invalid token format: ' . $exception->getMessage(), 0, $exception);
        }

        if (!isset($data['payload'], $data['exp'])) {
            throw new \RuntimeException('Invalid token structure');
        }

        if ($this->clock->now()->getTimestamp() >= $data['exp']) {
            throw new \RuntimeException('Token has expired');
        }

        return $data['payload'];
    }
}
