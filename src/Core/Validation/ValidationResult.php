<?php

declare(strict_types=1);

namespace Bgl\Core\Validation;

final readonly class ValidationResult
{
    /**
     * @param array<string, list<string>> $errors
     */
    public function __construct(
        private array $errors = [],
    ) {
    }

    public function hasErrors(): bool
    {
        return $this->errors !== [];
    }

    /**
     * @return array<string, list<string>>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    public function addError(string $field, string $message): self
    {
        $errors = $this->errors;
        $errors[$field][] = $message;

        return new self($errors);
    }
}
