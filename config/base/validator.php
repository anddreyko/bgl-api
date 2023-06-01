<?php

declare(strict_types=1);

use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

return [
    ValidatorInterface::class => Validation::createValidatorBuilder()
        ->enableAnnotationMapping()
        ->getValidator(),
];
