<?php

declare(strict_types=1);

use Bgl\Core\Validation\InputValidator;
use Bgl\Infrastructure\Validation\AttributeInputValidator;

return [
    InputValidator::class => DI\get(AttributeInputValidator::class),
];
