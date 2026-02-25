<?php

declare(strict_types=1);

namespace Bgl\Core\Auth;

interface TokenIssuer
{
    /**
     * Issue a new access + refresh token pair for the given user.
     *
     * @throws AuthenticationException if user cannot be issued tokens
     */
    public function issue(string $userId): TokenPair;
}
