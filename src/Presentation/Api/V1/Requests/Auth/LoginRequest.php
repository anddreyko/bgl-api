<?php

declare(strict_types=1);

namespace Bgl\Presentation\Api\V1\Requests\Auth;

use Bgl\Application\Handlers\Auth\LoginByCredentials\Command;

final readonly class LoginRequest implements Command
{
    public function __construct(
        private string $username,
        private string $password,
    ) {
    }

    #[\Override]
    public function getUsername(): string
    {
        return $this->username;
    }

    #[\Override]
    public function getPassword(): string
    {
        return $this->password;
    }
}
