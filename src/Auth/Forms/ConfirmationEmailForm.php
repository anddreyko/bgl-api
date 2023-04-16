<?php

declare(strict_types=1);

namespace App\Auth\Forms;

final readonly class ConfirmationEmailForm
{
    public function __construct(public string $token)
    {
    }
}
