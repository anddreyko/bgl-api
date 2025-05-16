<?php

declare(strict_types=1);

namespace App\Infrastructure\Validation;

class ValidationException extends \InvalidArgumentException
{
    /**
     * @param string[] $errors
     */
    public function __construct(private readonly array $errors)
    {
        parent::__construct();
    }

    /**
     * @return string[] $errors
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
