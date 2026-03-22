<?php

declare(strict_types=1);

namespace Bgl\Domain\Profile;

use Bgl\Core\ValueObjects\DateTime;
use Bgl\Core\ValueObjects\Email;
use Bgl\Core\ValueObjects\Uuid;

final class User
{
    public function __construct(
        public Uuid $id,
        private readonly Email $email,
        private string $passwordHash,
        private readonly DateTime $createdAt,
        private UserStatus $status,
        private int $tokenVersion = 1,
        private ?string $name = null,
    ) {
    }

    public static function register(
        Uuid $id,
        Email $email,
        string $passwordHash,
        DateTime $createdAt,
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
        return 'Player' . str_pad((string)random_int(0, 9999), 4, '0', STR_PAD_LEFT);
    }

    public function confirm(): void
    {
        if ($this->status === UserStatus::Active) {
            throw new UserAlreadyConfirmedException();
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

    public function resetPassword(string $passwordHash): void
    {
        $this->passwordHash = $passwordHash;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function getStatus(): UserStatus
    {
        return $this->status;
    }

    public function rename(string $name): void
    {
        if (\preg_match('/^[a-zA-Z0-9]+$/', $name) !== 1) {
            throw new InvalidNameException($name);
        }

        $this->name = $name;
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
