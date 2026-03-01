<?php

declare(strict_types=1);

namespace Bgl\Core\Security;

interface Tokenizer
{
    public function generate(TokenPayload $payload, int $ttlSeconds): string;

    public function verify(string $token): TokenPayload;
}
