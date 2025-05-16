<?php

declare(strict_types=1);

namespace App\Infrastructure\Tokens;

use App\Core\ValueObjects\Token;

/**
 * @see \Tests\Unit\Core\Tokens\Services\TokenizerServiceTest
 */
final readonly class Tokenizer
{
    public function __construct(private \DateInterval $duration)
    {
    }

    public function generate(\DateTimeImmutable $date): Token
    {
        return Token::create($date->add($this->duration));
    }
}
