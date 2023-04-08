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
final readonly class User
{
    public function __construct(
        private Id $id,
        private \DateTimeImmutable $date,
        private Email $email,
        private PasswordHash $hash,
        private ?Token $token,
        private UserStatusEnum $status = UserStatusEnum::Active,
    ) {
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

    public function getHash(): PasswordHash
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

    public function isActive(): bool
    {
        return $this->status->isActive();
    }

    public function isWait(): bool
    {
        return $this->status->isWait();
    }
}
