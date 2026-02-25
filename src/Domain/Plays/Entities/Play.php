<?php

declare(strict_types=1);

namespace Bgl\Domain\Plays\Entities;

use Bgl\Core\ValueObjects\Uuid;

final class Play
{
    public function __construct(
        private readonly Uuid $id,
        private readonly Uuid $userId,
        private readonly ?string $name,
        private PlayStatus $status,
        private readonly \DateTimeImmutable $startedAt,
        private ?\DateTimeImmutable $finishedAt,
    ) {
    }

    public static function open(
        Uuid $id,
        Uuid $userId,
        ?string $name,
        \DateTimeImmutable $startedAt,
    ): self {
        return new self($id, $userId, $name, PlayStatus::Draft, $startedAt, null);
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getUserId(): Uuid
    {
        return $this->userId;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getStatus(): PlayStatus
    {
        return $this->status;
    }

    public function getStartedAt(): \DateTimeImmutable
    {
        return $this->startedAt;
    }

    public function getFinishedAt(): ?\DateTimeImmutable
    {
        return $this->finishedAt;
    }

    public function close(\DateTimeImmutable $finishedAt): void
    {
        if ($this->status !== PlayStatus::Draft) {
            throw new \DomainException('Play can only be closed from draft status');
        }

        $this->status = PlayStatus::Published;
        $this->finishedAt = $finishedAt;
    }
}
