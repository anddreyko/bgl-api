<?php

declare(strict_types=1);

namespace Bgl\Domain\Profile\Entities;

use Bgl\Core\ValueObjects\DateTime;
use Bgl\Core\ValueObjects\Uuid;

final class PasskeyChallenge
{
    private function __construct(
        public Uuid $id,
        private readonly string $challenge,
        private readonly DateTime $expiresAt,
        private readonly ?Uuid $userId = null,
    ) {
    }

    public static function forRegistration(
        Uuid $id,
        string $challenge,
        DateTime $expiresAt,
        Uuid $userId,
    ): self {
        return new self($id, $challenge, $expiresAt, $userId);
    }

    public static function forLogin(
        Uuid $id,
        string $challenge,
        DateTime $expiresAt,
    ): self {
        return new self($id, $challenge, $expiresAt);
    }

    public function isExpired(DateTime $now): bool
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

    public function getExpiresAt(): DateTime
    {
        return $this->expiresAt;
    }

    public function getUserId(): ?Uuid
    {
        return $this->userId;
    }
}
