<?php

declare(strict_types=1);

namespace App\Auth\ValueObjects;

use Ramsey\Uuid\Uuid;
use Webmozart\Assert\Assert;

/**
 * @see \Tests\Unit\Auth\ValueObjects\IdTest
 */
final class Id
{
    public function __construct(private string $value)
    {
        Assert::uuid($value);

        $this->value = \mb_strtolower($value);
    }

    public static function create(): self
    {
        return new self(Uuid::uuid4()->toString());
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
