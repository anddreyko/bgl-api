<?php

declare(strict_types=1);

namespace Bgl\Core\Auth;

interface Authenticator
{
    /**
     * Authenticate user by email and password, return token pair.
     *
     * @throws AuthenticationException on failure
     */
    public function login(string $email, string $password): TokenPair;

    /**
     * Refresh token pair using a valid refresh token.
     *
     * @throws AuthenticationException on failure
     */
    public function refresh(string $refreshToken): TokenPair;

    /**
     * Revoke all tokens for the given user.
     *
     * @throws AuthenticationException if user not found
     */
    public function revoke(string $userId): void;

    /**
     * Verify access token and return auth payload.
     *
     * @throws AuthenticationException on failure
     */
    public function verify(string $accessToken): AuthPayload;
}
