<?php

declare(strict_types=1);

namespace Bgl\Core\Auth;

use Bgl\Core\ValueObjects\Uuid;

interface Verifier
{
    /**
     * Issue new credentials for the given user.
     *
     * @throws TooManyRequestsException if rate limit exceeded
     */
    public function issue(Uuid $userId): Credentials;

    /**
     * Confirm a credential and return the associated user ID.
     *
     * @param non-empty-string $credential
     *
     * @throws InvalidConfirmationTokenException if credential is invalid
     * @throws ExpiredConfirmationTokenException  if credential has expired
     * @throws TooManyAttemptsException           if too many failed attempts
     */
    public function confirm(string $credential, CredentialType $type): Uuid;

    /**
     * Check whether new credentials can be issued for the given user.
     */
    public function canIssue(Uuid $userId): bool;
}
