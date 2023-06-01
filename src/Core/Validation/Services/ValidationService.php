<?php

declare(strict_types=1);

namespace App\Core\Validation\Services;

use App\Core\Validation\Exceptions\ValidationException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final readonly class ValidationService
{
    public function __construct(private ValidatorInterface $validator)
    {
    }

    public function validate(mixed $data): void
    {
        /** @var string[] $errors */
        $errors = [];
        foreach ($this->validator->validate($data) as $error) {
            $errors[$error->getPropertyPath()] = (string)$error->getMessage();
        }

        if (!empty($errors)) {
            throw new ValidationException($errors);
        }
    }
}
