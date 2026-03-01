<?php

declare(strict_types=1);

namespace Bgl\Core\Validation;

/**
 * Typed wrapper for field-level validation errors.
 *
 * @implements \IteratorAggregate<string, list<string>>
 */
final readonly class ValidationErrors implements \IteratorAggregate, \Countable
{
    /**
     * @param array<string, list<string>> $errors
     */
    private function __construct(
        private array $errors,
    ) {
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /**
     * @param array<string, list<string>> $errors
     */
    public static function fromArray(array $errors): self
    {
        return new self($errors);
    }

    public function isEmpty(): bool
    {
        return $this->errors === [];
    }

    public function hasField(string $field): bool
    {
        return array_key_exists($field, $this->errors);
    }

    /**
     * @return list<string>
     */
    public function forField(string $field): array
    {
        return $this->errors[$field] ?? [];
    }

    public function withError(string $field, string $message): self
    {
        $errors = $this->errors;
        $errors[$field][] = $message;

        return new self($errors);
    }

    /**
     * @return array<string, list<string>>
     */
    public function toArray(): array
    {
        return $this->errors;
    }

    /**
     * @return \ArrayIterator<string, list<string>>
     */
    #[\Override]
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->errors);
    }

    #[\Override]
    public function count(): int
    {
        return count($this->errors);
    }
}
