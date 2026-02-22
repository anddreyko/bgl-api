# Documentation: Schema-Based Request Mapping

## Overview

Implemented a unified API entry point (`ApiAction`) that maps HTTP requests to Command/Query messages using OpenAPI configuration with `x-*` extensions, then dispatches them through the message bus.

## Components

### Core Contracts

- `src/Core/Http/SchemaMapper.php` -- interface for mapping HTTP request data to message constructor arguments.

### Presentation Layer

- `src/Presentation/Api/ApiAction.php` -- single entry point for all API routes. Matches route, runs interceptors, maps request data, creates message, dispatches, serializes result, returns JSON response.
- `src/Presentation/Api/RouteMap.php` -- matches HTTP method + URL path against OpenAPI `paths` config. Supports path parameters (e.g. `{token}`), extracts `x-message`, `x-interceptors`, and schema from config.
- `src/Presentation/Api/MatchedOperation.php` -- value object holding matched route info (message class, interceptors, path params, schema).
- `src/Presentation/Api/InterceptorPipeline.php` -- resolves interceptor classes from DI container and executes them in order, passing request through the chain.
- `src/Presentation/Api/Interceptors/Interceptor.php` -- interface for request interceptors (auth, rate limiting, etc.).

### Infrastructure

- `src/Infrastructure/Http/OpenApiSchemaMapper.php` -- implements `SchemaMapper`. Extracts values from path params, request body, and query params based on OpenAPI schema. Supports `x-target` for property name mapping and type casting (`|int`, `|bool`, `|float`, `|datetime`).

### Configuration

- `config/common/api-action.php` -- DI wiring for `RouteMap`, `InterceptorPipeline`, `ApiAction`.
- `config/common/http.php` -- DI alias `SchemaMapper -> OpenApiSchemaMapper`.
- `config/common/openapi/ping.php` -- updated with `x-message` extension pointing to `Ping\Command`.

## OpenAPI x-extensions

Routes are configured via PHP arrays in `config/common/openapi/*.php`:

```php
'/ping' => [
    'get' => [
        'summary' => 'Health check',
        'x-message' => Command::class,         // Message class to instantiate
        'x-interceptors' => [...],              // Optional interceptor classes
        'requestBody' => [                      // Optional request body schema
            'content' => [
                'application/json' => [
                    'schema' => [
                        'properties' => [
                            'email' => [
                                'type' => 'string',
                                'x-target' => 'email',        // Maps to constructor param
                            ],
                            'age' => [
                                'type' => 'integer',
                                'x-target' => 'age|int',      // With type cast
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
],
```

## Request Flow

1. `web/index.php` catches all routes via Slim `any('/{path:.*}', ...)`
2. `ApiAction::handle()` is called with the PSR-7 request
3. `RouteMap::match()` finds the matching operation from OpenAPI config
4. `InterceptorPipeline::process()` runs any configured interceptors
5. `SchemaMapper::map()` extracts and casts request data
6. Message is created with mapped data as named constructor arguments
7. Message is dispatched via `Dispatcher`
8. Result is serialized and returned as JSON

## Error Handling

- No route match: 404 with `ErrorResponse`
- `DomainException`: 400 with error message
- `InvalidArgumentException`: 422 (validation error)
- Other `Throwable`: 500 (with trace in debug mode)

## Tests

- Unit: `RouteMapCest` (7 tests), `InterceptorPipelineCest` (3 tests), `OpenApiSchemaMapperCest` (10 tests)
- Functional: `ApiActionCest` (3 tests) -- full integration with DI container

## Bug Fix

Fixed `config/common/serializer.php`: `Serializer::class => FractalSerializer::class` was a string alias that PHP-DI did not resolve as a reference. Changed to `DI\get(FractalSerializer::class)`.
