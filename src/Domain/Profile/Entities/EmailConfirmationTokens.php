<?php

declare(strict_types=1);

namespace Bgl\Domain\Profile\Entities;

interface EmailConfirmationTokens
{
    public function add(EmailConfirmationToken $token): void;

    public function findByToken(string $token): ?EmailConfirmationToken;

    public function remove(EmailConfirmationToken $token): void;
}
