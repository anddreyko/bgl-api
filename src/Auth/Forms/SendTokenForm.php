<?php

declare(strict_types=1);

namespace App\Auth\Forms;

class SendTokenForm
{
    public function __construct(public string $email)
    {
    }
}
