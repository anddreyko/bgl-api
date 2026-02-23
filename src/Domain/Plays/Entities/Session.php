<?php

declare(strict_types=1);

namespace Bgl\Domain\Plays\Entities;

use Bgl\Core\ValueObjects\Uuid;

final class Session
{
    public function __construct(
        private readonly Uuid $id,
        private readonly Uuid $userId,
        private readonly ?string $name,
        private SessionStatus $status,
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
        return new self($id, $userId, $name, SessionStatus::Draft, $startedAt, null);
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

    public function getStatus(): SessionStatus
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
        if ($this->status !== SessionStatus::Draft) {
            throw new \DomainException('Session can only be closed from draft status');
        }

        $this->status = SessionStatus::Published;
        $this->finishedAt = $finishedAt;
    }
}
