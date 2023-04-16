<?php

declare(strict_types=1);

namespace App\Auth\Entities;

use App\Auth\Enums\UserStatusEnum;
use App\Auth\ValueObjects\Email;
use App\Auth\ValueObjects\Id;
use App\Auth\ValueObjects\PasswordHash;
use App\Auth\ValueObjects\Token;

/**
 * @see \Tests\Unit\Auth\Entities\UserTest
 */
final class User
{
    private ?PasswordHash $hash = null;
    private ?Token $token = null;

    private function __construct(
        private readonly Id $id,
        private readonly \DateTimeImmutable $date,
        private readonly Email $email,
        private UserStatusEnum $status,
    ) {
    }

    public static function createByEmail(
        Id $id,
        \DateTimeImmutable $date,
        Email $email,
        PasswordHash $hash,
        Token $token
    ): self {
        $user = new self($id, $date, $email, UserStatusEnum::Wait);
        $user->hash = $hash;
        $user->token = $token;

        return $user;
    }

    public function getId(): Id
    {
        return $this->id;
    }

    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function getHash(): ?PasswordHash
    {
        return $this->hash;
    }

    public function getToken(): ?Token
    {
        return $this->token;
    }

    public function getStatus(): UserStatusEnum
    {
        return $this->status;
    }

    public function setStatus(UserStatusEnum $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->status->isActive();
    }

    public function isWait(): bool
    {
        return $this->status->isWait();
    }
}
