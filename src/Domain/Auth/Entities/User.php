<?php

declare(strict_types=1);

namespace Bgl\Domain\Auth\Entities;

use Bgl\Core\ValueObjects\Email;
use Bgl\Core\ValueObjects\Uuid;

final class User
{
    public function __construct(
        public Uuid $id,
        private readonly Email $email,
        private readonly string $passwordHash,
        private readonly \DateTimeImmutable $createdAt,
        private UserStatus $status,
        private int $tokenVersion = 1,
    ) {
    }

    public static function register(
        Uuid $id,
        Email $email,
        string $passwordHash,
        \DateTimeImmutable $createdAt,
    ): self {
        return new self($id, $email, $passwordHash, $createdAt, UserStatus::Inactive);
    }

    public function confirm(): void
    {
        if ($this->status === UserStatus::Active) {
            throw new \DomainException('User is already confirmed.');
        }

        $this->status = UserStatus::Active;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getStatus(): UserStatus
    {
        return $this->status;
    }

    public function getTokenVersion(): int
    {
        return $this->tokenVersion;
    }

    public function incrementTokenVersion(): void
    {
        $this->tokenVersion++;
    }
}
