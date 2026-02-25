<?php

declare(strict_types=1);

namespace Bgl\Core\Auth;

interface PasskeyVerifier
{
    /**
     * Generate WebAuthn registration options JSON + challenge.
     */
    public function registerOptions(string $userId, string $userName): PasskeyOptions;

    /**
     * Verify registration response and return credential data.
     *
     * @throws AuthenticationException on verification failure
     */
    public function register(string $response, string $challenge): CredentialResult;

    /**
     * Generate WebAuthn login options JSON + challenge.
     */
    public function loginOptions(): PasskeyOptions;

    /**
     * Verify login response and return updated counter.
     *
     * @throws AuthenticationException on verification failure
     */
    public function login(string $response, string $challenge, string $credentialData): int;
}
