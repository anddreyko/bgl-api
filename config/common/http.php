<?php

declare(strict_types=1);

use Bgl\Core\Http\SchemaMapper;
use Bgl\Infrastructure\Http\OpenApiSchemaMapper;

return [
    SchemaMapper::class => DI\get(OpenApiSchemaMapper::class),
];
