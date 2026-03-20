<?php

declare(strict_types=1);

namespace Bgl\Tests\Support\Dummy;

use Bgl\Core\Auth\Credentials;
use Bgl\Core\Auth\CredentialType;
use Bgl\Core\Auth\ExpiredConfirmationTokenException;
use Bgl\Core\Auth\InvalidConfirmationTokenException;
use Bgl\Core\Auth\TooManyAttemptsException;
use Bgl\Core\Auth\TooManyRequestsException;
use Bgl\Core\Auth\Verifier;
use Bgl\Core\ValueObjects\Uuid;

final class FakeVerifier implements Verifier
{
    /** @var array<string, array{userId: Uuid, code: string}> */
    private array $tokens = [];

    /** @var array<string, string> */
    private array $codes = [];

    private ?Credentials $lastCredentials = null;

    private bool $rateLimited = false;

    /** @var array<string, true> */
    private array $expired = [];

    /** @var array<string, int> */
    private array $attempts = [];

    #[\Override]
    public function issue(Uuid $userId): Credentials
    {
        if ($this->rateLimited) {
            throw new TooManyRequestsException();
        }

        $code = (string) random_int(100000, 999999);
        $token = bin2hex(random_bytes(16));
        $this->tokens[$token] = ['userId' => $userId, 'code' => $code];
        $this->codes[$code] = $token;

        /** @var non-empty-string $code */
        /** @var non-empty-string $token */
        $this->lastCredentials = new Credentials($code, $token);

        return $this->lastCredentials;
    }

    #[\Override]
    public function confirm(string $credential, CredentialType $type): Uuid
    {
        if (isset($this->expired[$credential])) {
            throw new ExpiredConfirmationTokenException();
        }

        if (($this->attempts[$credential] ?? 0) >= 5) {
            throw new TooManyAttemptsException();
        }

        if ($type === CredentialType::Token) {
            return $this->confirmByToken($credential);
        }

        return $this->confirmByCode($credential);
    }

    #[\Override]
    public function canIssue(Uuid $userId): bool
    {
        return !$this->rateLimited;
    }

    public function getLastCredentials(): ?Credentials
    {
        return $this->lastCredentials;
    }

    public function setRateLimited(bool $limited): void
    {
        $this->rateLimited = $limited;
    }

    public function expireCredential(string $credential): void
    {
        $this->expired[$credential] = true;
    }

    private function confirmByToken(string $credential): Uuid
    {
        if (!isset($this->tokens[$credential])) {
            throw new InvalidConfirmationTokenException();
        }

        $userId = $this->tokens[$credential]['userId'];
        unset($this->tokens[$credential]);

        return $userId;
    }

    private function confirmByCode(string $credential): Uuid
    {
        if (!isset($this->codes[$credential])) {
            $this->attempts[$credential] = ($this->attempts[$credential] ?? 0) + 1;
            throw new InvalidConfirmationTokenException();
        }

        $token = $this->codes[$credential];
        $userId = $this->tokens[$token]['userId'];
        unset($this->codes[$credential], $this->tokens[$token]);

        return $userId;
    }
}
