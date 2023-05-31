<?php

declare(strict_types=1);

namespace App\Auth\ValueObjects;

use App\Auth\Exceptions\IncorrectPasswordException;
use Webmozart\Assert\Assert;

/**
 * @see \Tests\Unit\Auth\ValueObjects\PasswordHashTest
 */
final class PasswordHash
{
    public function __construct(private string $value)
    {
        $value = trim($value);
        try {
            Assert::notEmpty($value);
        } catch (\Exception $exception) {
            throw new IncorrectPasswordException($exception->getMessage(), $exception);
        }

        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
