<?php

declare(strict_types=1);

use Bgl\Core\Http\RequestValidator;
use Bgl\Core\Http\SchemaMapper;
use Bgl\Infrastructure\Http\OpenApiRequestValidator;
use Bgl\Infrastructure\Http\OpenApiSchemaMapper;

return [
    SchemaMapper::class => DI\get(OpenApiSchemaMapper::class),
    RequestValidator::class => DI\get(OpenApiRequestValidator::class),
];
