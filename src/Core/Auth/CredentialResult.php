<?php

declare(strict_types=1);

namespace Bgl\Core\Auth;

final readonly class CredentialResult
{
    public function __construct(
        public string $credentialId,
        public string $credentialData,
    ) {
    }
}
