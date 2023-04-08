<?php

declare(strict_types=1);

namespace App\Auth\Forms;

final readonly class ConfirmEmailForm
{
    public function __construct(public string $token)
    {
    }
}
