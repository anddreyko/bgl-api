<?php

declare(strict_types=1);

namespace Bgl\Domain\Auth\Entities;

use Bgl\Core\ValueObjects\Email;
use Bgl\Core\ValueObjects\Uuid;

final readonly class User
{
    public function __construct(
        public Uuid $id,
        private Email $email,
        private \DateTimeImmutable $createdAt,
        private UserStatus $status,
    ) {
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getStatus(): UserStatus
    {
        return $this->status;
    }
}
