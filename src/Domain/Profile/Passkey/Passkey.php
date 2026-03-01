<?php

declare(strict_types=1);

namespace Bgl\Domain\Profile\Passkey;

use Bgl\Core\ValueObjects\DateTime;
use Bgl\Core\ValueObjects\Uuid;

final class Passkey
{
    private function __construct(
        public Uuid $id,
        private readonly Uuid $userId,
        private readonly string $credentialId,
        private readonly string $credentialData,
        private int $counter,
        private readonly DateTime $createdAt,
        private readonly ?string $label = null,
    ) {
    }

    public static function create(
        Uuid $id,
        Uuid $userId,
        string $credentialId,
        string $credentialData,
        DateTime $createdAt,
        ?string $label = null,
    ): self {
        return new self($id, $userId, $credentialId, $credentialData, 0, $createdAt, $label);
    }

    public function updateCounter(int $counter): void
    {
        $this->counter = $counter;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getUserId(): Uuid
    {
        return $this->userId;
    }

    public function getCredentialId(): string
    {
        return $this->credentialId;
    }

    public function getCredentialData(): string
    {
        return $this->credentialData;
    }

    public function getCounter(): int
    {
        return $this->counter;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }
}
