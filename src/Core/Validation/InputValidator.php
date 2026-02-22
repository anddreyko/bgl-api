<?php

declare(strict_types=1);

namespace Bgl\Core\Validation;

interface InputValidator
{
    public function validate(object $target): ValidationResult;
}
