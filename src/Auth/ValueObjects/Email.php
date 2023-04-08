<?php

declare(strict_types=1);

namespace App\Auth\ValueObjects;

use Webmozart\Assert\Assert;

/**
 * @see \Tests\Unit\Auth\ValueObjects\EmailTest
 */
final class Email
{
    public function __construct(private string $value)
    {
        $value = trim($value);
        Assert::notEmpty($value);
        Assert::email($value);

        $this->value = \mb_strtolower($value);
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
