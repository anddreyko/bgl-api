<?php

declare(strict_types=1);

namespace Bgl\Core\ValueObjects;

final readonly class Uuid implements \Stringable
{
    private const string UUID_PATTERN = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';

    /**
     * @param non-empty-string|null $value
     */
    public function __construct(
        private ?string $value = null
    ) {
        if ($value !== null && preg_match(self::UUID_PATTERN, $value) !== 1) {
            throw new \InvalidArgumentException("Invalid UUID: {$value}");
        }
    }

    public static function isValid(string $value): bool
    {
        return preg_match(self::UUID_PATTERN, $value) === 1;
    }

    public function isNull(): bool
    {
        return null === $this->value;
    }

    /**
     * @return non-empty-string|null
     */
    public function getValue(): ?string
    {
        return $this->value;
    }

    #[\Override]
    public function __toString(): string
    {
        return $this->value ?? '';
    }
}
