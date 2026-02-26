<?php

declare(strict_types=1);

namespace Bgl\Tests\Support\Dummy;

use Bgl\Core\Auth\Confirmer;
use Bgl\Core\Auth\ExpiredConfirmationTokenException;
use Bgl\Core\Auth\InvalidConfirmationTokenException;
use Bgl\Core\ValueObjects\Uuid;

final class FakeConfirmer implements Confirmer
{
    /** @var array<string, Uuid> */
    private array $tokens = [];

    /** @var array<string, true> */
    private array $expiredTokens = [];

    private ?string $lastToken = null;

    #[\Override]
    public function request(Uuid $userId): void
    {
        $token = bin2hex(random_bytes(16));
        $this->tokens[$token] = $userId;
        $this->lastToken = $token;
    }

    #[\Override]
    public function confirm(string $token): Uuid
    {
        if (!isset($this->tokens[$token])) {
            throw new InvalidConfirmationTokenException();
        }

        if (isset($this->expiredTokens[$token])) {
            throw new ExpiredConfirmationTokenException();
        }

        $userId = $this->tokens[$token];
        unset($this->tokens[$token]);

        return $userId;
    }

    public function getLastToken(): ?string
    {
        return $this->lastToken;
    }

    public function expireToken(string $token): void
    {
        $this->expiredTokens[$token] = true;
    }
}
