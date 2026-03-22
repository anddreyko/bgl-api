<?php

declare(strict_types=1);

use Bgl\Core\Http\RequestValidator;
use Bgl\Core\Http\SchemaMapper;
use Bgl\Infrastructure\Http\HydratorMapper;
use Bgl\Infrastructure\Http\LeagueRequestValidator;

use function DI\get;

return [
    SchemaMapper::class => get(HydratorMapper::class),
    RequestValidator::class => get(LeagueRequestValidator::class),
];
