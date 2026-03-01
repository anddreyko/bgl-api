<?php

declare(strict_types=1);

namespace Bgl\Core\Validation;

final readonly class ValidationResult
{
    private ValidationErrors $errors;

    public function __construct(
        ValidationErrors|null $errors = null,
    ) {
        $this->errors = $errors ?? ValidationErrors::empty();
    }

    /**
     * @param array<string, list<string>> $errorsArray
     */
    public static function withErrors(array $errorsArray): self
    {
        return new self(ValidationErrors::fromArray($errorsArray));
    }

    public function hasErrors(): bool
    {
        return !$this->errors->isEmpty();
    }

    public function getErrors(): ValidationErrors
    {
        return $this->errors;
    }

    public function addError(string $field, string $message): self
    {
        return new self($this->errors->withError($field, $message));
    }
}
