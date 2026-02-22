<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Validation;

use Bgl\Core\Validation\Attributes\MinLength;
use Bgl\Core\Validation\Attributes\NotBlank;
use Bgl\Core\Validation\Attributes\ValidEmail;
use Bgl\Core\Validation\Attributes\ValidUuid;
use Bgl\Core\Validation\InputValidator;
use Bgl\Core\Validation\ValidationResult;

final readonly class AttributeInputValidator implements InputValidator
{
    #[\Override]
    public function validate(object $target): ValidationResult
    {
        $result = new ValidationResult();
        $reflection = new \ReflectionClass($target);
        $constructor = $reflection->getConstructor();

        if ($constructor === null) {
            return $result;
        }

        foreach ($constructor->getParameters() as $param) {
            $name = $param->getName();
            $prop = $reflection->getProperty($name);

            /** @var mixed $value */
            $value = $prop->getValue($target);

            foreach ($param->getAttributes() as $attr) {
                $instance = $attr->newInstance();
                $result = $this->validateAttribute($result, $name, $value, $instance);
            }
        }

        return $result;
    }

    private function validateAttribute(
        ValidationResult $result,
        string $field,
        mixed $value,
        object $attribute,
    ): ValidationResult {
        return match (true) {
            $attribute instanceof NotBlank => $this->validateNotBlank($result, $field, $value, $attribute),
            $attribute instanceof ValidEmail => $this->validateEmail($result, $field, $value, $attribute),
            $attribute instanceof MinLength => $this->validateMinLength($result, $field, $value, $attribute),
            $attribute instanceof ValidUuid => $this->validateUuid($result, $field, $value, $attribute),
            default => $result,
        };
    }

    private function validateNotBlank(
        ValidationResult $result,
        string $field,
        mixed $value,
        NotBlank $attribute,
    ): ValidationResult {
        if ($value === null || (is_string($value) && trim($value) === '')) {
            return $result->addError($field, $attribute->message);
        }

        return $result;
    }

    private function validateEmail(
        ValidationResult $result,
        string $field,
        mixed $value,
        ValidEmail $attribute,
    ): ValidationResult {
        if (!is_string($value) || filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
            return $result->addError($field, $attribute->message);
        }

        return $result;
    }

    private function validateMinLength(
        ValidationResult $result,
        string $field,
        mixed $value,
        MinLength $attribute,
    ): ValidationResult {
        if (is_string($value) && mb_strlen($value) < $attribute->min) {
            return $result->addError($field, sprintf($attribute->message, $attribute->min));
        }

        return $result;
    }

    private function validateUuid(
        ValidationResult $result,
        string $field,
        mixed $value,
        ValidUuid $attribute,
    ): ValidationResult {
        $pattern = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';

        if (!is_string($value) || preg_match($pattern, $value) !== 1) {
            return $result->addError($field, $attribute->message);
        }

        return $result;
    }
}
