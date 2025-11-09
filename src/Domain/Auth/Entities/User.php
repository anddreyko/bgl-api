<?php

declare(strict_types=1);

namespace Bgl\Domain\Auth\Entities;

final readonly class User
{
    public function __construct(
        private string $username,
        private string $password
    ) {
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getPassword(): string
    {
        return $this->password;
    }
}
