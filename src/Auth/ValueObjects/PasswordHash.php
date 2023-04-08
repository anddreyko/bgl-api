<?php

declare(strict_types=1);

namespace App\Auth\ValueObjects;

use Webmozart\Assert\Assert;

/**
 * @see \Tests\Unit\Auth\ValueObjects\PasswordHashTest
 */
final class PasswordHash
{
    public function __construct(private string $value)
    {
        $value = trim($value);
        Assert::notEmpty($value);

        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
