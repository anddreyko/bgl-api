<?php

declare(strict_types=1);

namespace App\Auth\Forms;

class IdentificationForm
{
    public function __construct(public string $email, public string $password)
    {
    }
}
