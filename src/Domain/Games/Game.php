<?php

declare(strict_types=1);

namespace Bgl\Domain\Games;

use Bgl\Core\ValueObjects\DateTime;
use Bgl\Core\ValueObjects\Uuid;

final readonly class Game
{
    private function __construct(
        private Uuid $id,
        private int $bggId,
        private string $name,
        private ?int $yearPublished,
        private DateTime $createdAt,
        private DateTime $updatedAt,
    ) {
    }

    public static function create(
        Uuid $id,
        int $bggId,
        string $name,
        ?int $yearPublished,
        DateTime $createdAt,
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

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }
}
