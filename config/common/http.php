<?php

declare(strict_types=1);

use Bgl\Core\Http\RequestValidator;
use Bgl\Core\Http\SchemaMapper;
use Bgl\Infrastructure\Http\HydratorMapper;
use Bgl\Infrastructure\Http\LeagueRequestValidator;

return [
    SchemaMapper::class => DI\get(HydratorMapper::class),
    RequestValidator::class => DI\get(LeagueRequestValidator::class),
];
