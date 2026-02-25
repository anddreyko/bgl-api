<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Auth;

use Bgl\Core\Auth\CredentialResult;
use Bgl\Core\Auth\PasskeyOptions;
use Bgl\Core\Auth\PasskeyVerifier;

final class FakePasskeyVerifier implements PasskeyVerifier
{
    #[\Override]
    public function registerOptions(string $userId, string $userName): PasskeyOptions
    {
        $challenge = base64_encode(random_bytes(32));

        return new PasskeyOptions(
            json_encode(['challenge' => $challenge, 'rp' => ['name' => 'test']], JSON_THROW_ON_ERROR),
            $challenge,
        );
    }

    #[\Override]
    public function register(string $response, string $challenge): CredentialResult
    {
        return new CredentialResult(
            credentialId: 'fake-credential-' . bin2hex(random_bytes(8)),
            credentialData: 'fake-credential-data',
        );
    }

    #[\Override]
    public function loginOptions(): PasskeyOptions
    {
        $challenge = base64_encode(random_bytes(32));

        return new PasskeyOptions(
            json_encode(['challenge' => $challenge], JSON_THROW_ON_ERROR),
            $challenge,
        );
    }

    #[\Override]
    public function login(string $response, string $challenge, string $credentialData): int
    {
        return 1;
    }
}
