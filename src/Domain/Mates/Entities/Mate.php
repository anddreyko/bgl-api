<?php

declare(strict_types=1);

namespace Bgl\Domain\Mates\Entities;

use Bgl\Core\ValueObjects\Uuid;

final class Mate
{
    private function __construct(
        private readonly Uuid $id,
        private readonly Uuid $userId,
        private string $name,
        private ?string $notes,
        private ?\DateTimeImmutable $deletedAt,
        private readonly \DateTimeImmutable $createdAt,
    ) {
    }

    public static function create(
        Uuid $id,
        Uuid $userId,
        string $name,
        ?string $notes,
        \DateTimeImmutable $createdAt,
    ): self {
        return new self($id, $userId, $name, $notes, null, $createdAt);
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getUserId(): Uuid
    {
        return $this->userId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function getDeletedAt(): ?\DateTimeImmutable
    {
        return $this->deletedAt;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function update(string $name, ?string $notes): void
    {
        $this->name = $name;
        $this->notes = $notes;
    }

    public function softDelete(\DateTimeImmutable $deletedAt): void
    {
        if ($this->deletedAt !== null) {
            throw new \DomainException('Mate is already deleted');
        }

        $this->deletedAt = $deletedAt;
    }

    public function isDeleted(): bool
    {
        return $this->deletedAt !== null;
    }
}
