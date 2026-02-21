<?php

declare(strict_types=1);

namespace Bgl\Core\Auth;

interface Identities
{
    /**
     * Find identity by username and password credentials.
     */
    public function findByCredentials(string $username, string $password): ?Identity;

    /**
     * Find identity by user ID.
     */
    public function findById(string $id): ?Identity;
}
