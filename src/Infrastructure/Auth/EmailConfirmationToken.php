<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Auth;

use Bgl\Core\ValueObjects\DateTime;
use Bgl\Core\ValueObjects\Uuid;

final class EmailConfirmationToken
{
    private function __construct(
        public Uuid $id,
        private readonly Uuid $userId,
        private readonly string $token,
        private readonly DateTime $expiresAt,
    ) {
    }

    public static function create(
        Uuid $id,
        Uuid $userId,
        string $token,
        DateTime $expiresAt,
    ): self {
        return new self($id, $userId, $token, $expiresAt);
    }

    public function isExpired(DateTime $now): bool
    {
        return $now->getValue() > $this->expiresAt->getValue();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getUserId(): Uuid
    {
        return $this->userId;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getExpiresAt(): DateTime
    {
        return $this->expiresAt;
    }
}
