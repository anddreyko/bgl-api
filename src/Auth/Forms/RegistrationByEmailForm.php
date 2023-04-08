<?php

declare(strict_types=1);

namespace App\Auth\Forms;

final readonly class RegistrationByEmailForm
{
    public function __construct(public string $email, public string $password)
    {
    }
}
