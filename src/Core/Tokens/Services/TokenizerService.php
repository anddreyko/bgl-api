<?php

declare(strict_types=1);

namespace App\Core\Tokens\Services;

use App\Auth\ValueObjects\Token;

/**
 * @see \Tests\Unit\Core\Tokens\Services\TokenizerServiceTest
 */
final readonly class TokenizerService
{
    public function __construct(private \DateInterval $duration)
    {
    }

    public function generate(\DateTimeImmutable $date): Token
    {
        return Token::create($date->add($this->duration));
    }
}
