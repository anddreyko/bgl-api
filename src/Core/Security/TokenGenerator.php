<?php

declare(strict_types=1);

namespace Bgl\Core\Security;

interface TokenGenerator
{
    /**
     * @param array<string, mixed> $payload
     */
    public function generate(array $payload, int $ttlSeconds): string;

    /**
     * @return array<string, mixed>
     */
    public function verify(string $token): array;
}
