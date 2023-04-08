<?php

declare(strict_types=1);

namespace App\Auth\Helpers;

use App\Auth\ValueObjects\Token;

final readonly class TokenizerHelper
{
    public function __construct(private \DateInterval $duration)
    {
    }

    public function generate(\DateTimeImmutable $date): Token
    {
        return Token::create($date->add($this->duration));
    }
}
