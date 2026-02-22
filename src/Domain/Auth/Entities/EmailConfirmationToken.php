<?php

declare(strict_types=1);

namespace Bgl\Domain\Auth\Entities;

use Bgl\Core\ValueObjects\Uuid;

final class EmailConfirmationToken
{
    private function __construct(
        public Uuid $id,
        private Uuid $userId,
        private string $token,
        private \DateTimeImmutable $expiresAt,
    ) {
    }

    public static function create(
        Uuid $id,
        Uuid $userId,
        string $token,
        \DateTimeImmutable $expiresAt,
    ): self {
        return new self($id, $userId, $token, $expiresAt);
    }

    public function isExpired(\DateTimeImmutable $now): bool
    {
        return $now > $this->expiresAt;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getUserId(): Uuid
    {
        return $this->userId;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getExpiresAt(): \DateTimeImmutable
    {
        return $this->expiresAt;
    }
}
