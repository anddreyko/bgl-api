<?php

declare(strict_types=1);

namespace Bgl\Domain\Locations;

use Bgl\Core\ValueObjects\DateTime;
use Bgl\Core\ValueObjects\Uuid;

final class Location
{
    private function __construct(
        private readonly Uuid $id,
        private readonly Uuid $userId,
        private string $name,
        private ?string $address,
        private ?string $notes,
        private ?string $url,
        private ?DateTime $deletedAt,
        private readonly DateTime $createdAt,
    ) {
    }

    public static function create(
        Uuid $id,
        Uuid $userId,
        string $name,
        ?string $address,
        ?string $notes,
        ?string $url,
        DateTime $createdAt,
    ): self {
        return new self($id, $userId, $name, $address, $notes, $url, null, $createdAt);
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

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function getDeletedAt(): ?DateTime
    {
        return $this->deletedAt;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function update(string $name, ?string $address, ?string $notes, ?string $url): void
    {
        $this->name = $name;
        $this->address = $address;
        $this->notes = $notes;
        $this->url = $url;
    }

    public function softDelete(DateTime $deletedAt): void
    {
        if ($this->deletedAt !== null) {
            throw new LocationAlreadyDeletedException();
        }

        $this->deletedAt = $deletedAt;
    }

    public function isDeleted(): bool
    {
        return $this->deletedAt !== null;
    }
}
