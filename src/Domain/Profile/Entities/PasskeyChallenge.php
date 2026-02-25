<?php

declare(strict_types=1);

namespace Bgl\Domain\Profile\Entities;

use Bgl\Core\ValueObjects\Uuid;

final class PasskeyChallenge
{
    private function __construct(
        public Uuid $id,
        private readonly string $challenge,
        private readonly \DateTimeImmutable $expiresAt,
        private readonly ?Uuid $userId = null,
    ) {
    }

    public static function forRegistration(
        Uuid $id,
        string $challenge,
        \DateTimeImmutable $expiresAt,
        Uuid $userId,
    ): self {
        return new self($id, $challenge, $expiresAt, $userId);
    }

    public static function forLogin(
        Uuid $id,
        string $challenge,
        \DateTimeImmutable $expiresAt,
    ): self {
        return new self($id, $challenge, $expiresAt);
    }

    public function isExpired(\DateTimeImmutable $now): bool
    {
        return $now >= $this->expiresAt;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getChallenge(): string
    {
        return $this->challenge;
    }

    public function getExpiresAt(): \DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function getUserId(): ?Uuid
    {
        return $this->userId;
    }
}
