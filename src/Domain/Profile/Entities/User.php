<?php

declare(strict_types=1);

namespace Bgl\Domain\Profile\Entities;

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
        private readonly ?string $name = null,
        private int $version = 1,
    ) {
    }

    public static function register(
        Uuid $id,
        Email $email,
        string $passwordHash,
        \DateTimeImmutable $createdAt,
        ?string $name = null,
    ): self {
        return new self(
            $id,
            $email,
            $passwordHash,
            $createdAt,
            UserStatus::Inactive,
            name: $name ?? self::generateDefaultName(),
        );
    }

    private static function generateDefaultName(): string
    {
        return 'User#' . str_pad((string)random_int(0, 9999), 4, '0', STR_PAD_LEFT);
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

    public function getName(): string
    {
        return $this->name ?? self::generateDefaultName();
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
