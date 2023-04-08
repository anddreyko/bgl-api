<?php

declare(strict_types=1);

namespace App\Auth\ValueObjects;

use Ramsey\Uuid\Uuid;
use Webmozart\Assert\Assert;

/**
 * @see \Tests\Unit\Auth\ValueObjects\TokenTest
 */
final class Token
{
    public function __construct(private string $value, private readonly \DateTimeImmutable $expires)
    {
        $value = trim($value);
        Assert::uuid($value);
        $this->value = \mb_strtolower($value);
    }

    public static function create(\DateTimeImmutable $expires): self
    {
        return new self(Uuid::uuid4()->toString(), $expires);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getExpires(): \DateTimeImmutable
    {
        return $this->expires;
    }

    public function check(string $value): bool
    {
        try {
            $this->eq($value);
            $this->isExpire();
        } catch (\Exception) {
            return false;
        }

        return true;
    }

    private function eq(string $value): void
    {
        Assert::eq($value, $this->value);
    }

    private function isExpire(): void
    {
        Assert::lessThan(time(), $this->expires->getTimestamp());
    }
}
