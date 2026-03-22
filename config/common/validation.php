<?php

declare(strict_types=1);

use Bgl\Core\Validation\InputValidator;
use Bgl\Infrastructure\Validation\AttributeInputValidator;

use function DI\get;

return [
    InputValidator::class => get(AttributeInputValidator::class),
];
