<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Auth;

use Bgl\Core\ValueObjects\DateTime;
use Bgl\Core\ValueObjects\Uuid;

final class VerificationToken
{
    private function __construct(
        public Uuid $id,
        private readonly Uuid $userId,
        private readonly string $codeHash,
        private readonly string $token,
        private readonly DateTime $expiresAt,
        private int $attemptCount,
        private readonly DateTime $createdAt,
    ) {
    }

    public static function create(
        Uuid $id,
        Uuid $userId,
        string $codeHash,
        string $token,
        DateTime $expiresAt,
        DateTime $createdAt,
    ): self {
        return new self($id, $userId, $codeHash, $token, $expiresAt, 0, $createdAt);
    }

    public function isExpired(DateTime $now): bool
    {
        return $now->getValue() > $this->expiresAt->getValue();
    }

    public function incrementAttempts(): void
    {
        ++$this->attemptCount;
    }

    public function getAttemptCount(): int
    {
        return $this->attemptCount;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getUserId(): Uuid
    {
        return $this->userId;
    }

    public function getCodeHash(): string
    {
        return $this->codeHash;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getExpiresAt(): DateTime
    {
        return $this->expiresAt;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }
}
