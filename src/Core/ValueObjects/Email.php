<?php

declare(strict_types=1);

namespace App\Core\ValueObjects;

use App\Auth\Exceptions\IncorrectEmailException;
use Webmozart\Assert\Assert;

/**
 * @see \Tests\Unit\Auth\ValueObjects\EmailTest
 */
final class Email
{
    public function __construct(private string $value)
    {
        try {
            $value = trim($value);
            Assert::notEmpty($value);
            Assert::email($value);
        } catch (\Exception $exception) {
            throw new IncorrectEmailException($exception->getMessage(), $exception);
        }

        $this->value = \mb_strtolower($value);
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
