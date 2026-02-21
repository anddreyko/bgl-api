<?php

declare(strict_types=1);

namespace Bgl\Core\Auth;

interface Authentificator
{
    /**
     * Authenticate user with given grant type and credentials.
     *
     * @param GrantType $grant The grant type to use
     * @param array<string, mixed> $credentials Grant-specific credentials
     *
     * @throws AuthenticationException on authentication failure
     */
    public function authenticate(GrantType $grant, array $credentials): Identity;

    /**
     * Check if this authenticator supports the given grant type.
     */
    public function supports(GrantType $grant): bool;
}
