<?php

declare(strict_types=1);

namespace App\Auth\Forms;

class UpdatePasswordForm
{
    public function __construct(public string $token, public string $password)
    {
    }
}
