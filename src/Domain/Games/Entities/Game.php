<?php

declare(strict_types=1);

namespace Bgl\Domain\Games\Entities;

use Bgl\Core\ValueObjects\Uuid;

final class Game
{
    private function __construct(
        private readonly Uuid $id,
        private readonly int $bggId,
        private string $name,
        private ?int $yearPublished,
        private readonly \DateTimeImmutable $createdAt,
        private \DateTimeImmutable $updatedAt,
    ) {
    }

    public static function create(
        Uuid $id,
        int $bggId,
        string $name,
        ?int $yearPublished,
        \DateTimeImmutable $createdAt,
    ): self {
        return new self($id, $bggId, $name, $yearPublished, $createdAt, $createdAt);
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getBggId(): int
    {
        return $this->bggId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getYearPublished(): ?int
    {
        return $this->yearPublished;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function updateFromCatalog(string $name, ?int $yearPublished, \DateTimeImmutable $updatedAt): void
    {
        $this->name = $name;
        $this->yearPublished = $yearPublished;
        $this->updatedAt = $updatedAt;
    }
}
