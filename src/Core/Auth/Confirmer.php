<?php

declare(strict_types=1);

namespace Bgl\Core\Auth;

use Bgl\Core\ValueObjects\Uuid;

interface Confirmer
{
    /**
     * Create a confirmation token for the given user.
     *
     * @throws \RuntimeException on failure
     */
    public function request(Uuid $userId): void;

    /**
     * Confirm email by token and return the associated user ID.
     *
     * @throws InvalidConfirmationTokenException if token is not found or user does not exist
     * @throws ExpiredConfirmationTokenException if token has expired
     */
    public function confirm(string $token): Uuid;
}
