<?php

declare(strict_types=1);

namespace App\Core\ValueObjects;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embeddable;
use Doctrine\ORM\Mapping\Id;
use Ramsey\Uuid\Uuid;
use Webmozart\Assert\Assert;

/**
 * @see \Tests\Unit\Auth\ValueObjects\TokenTest
 */
#[Embeddable]
final class Token
{
    /** @var string */
    #[Id]
    #[Column(type: \Doctrine\DBAL\Types\Types::STRING, nullable: true)]
    private string $value;

    /** @var \DateTimeImmutable */
    #[Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private \DateTimeImmutable $expires;

    public function __construct(
        string $value,
        \DateTimeImmutable $expires
    ) {
        $this->expires = $expires;
        $this->value = $value;
        $value = \trim($value);
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

    public function isEmpty(): bool
    {
        return empty($this->value);
    }

    public function validate(string $value): bool
    {
        try {
            $this->eq($value);
            $this->expires();
        } catch (\Exception) {
            return false;
        }

        return true;
    }

    private function eq(string $value): void
    {
        Assert::eq($value, $this->value);
    }

    public function isExpire(): bool
    {
        try {
            Assert::lessThan(time(), $this->expires->getTimestamp());
        } catch (\Exception) {
            return true;
        }

        return false;
    }

    public function expires(): void
    {
        Assert::lessThan(time(), $this->expires->getTimestamp());
    }
}
