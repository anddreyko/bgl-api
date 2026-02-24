# Documentation: CORE-009 Compiled Route Map + Hydrator Mapper

## Summary

Replaced the HTTP-to-Message pipeline with a compiled route map and hydrator-based mapper:

- **RouteMap** (O(N) regex per request) replaced by **CompiledRouteMap** (O(1) HashMap for static routes + single compiled regex for dynamic routes)
- **OpenApiSchemaMapper** (148 lines, manual `x-target`/`x-source` mapping) replaced by **HydratorMapper** (~40 lines, clean param collection)
- **Manual `new $messageClass(...$data)`** replaced by **EventSauce ObjectHydrator** (`hydrateObject()`) for type-safe hydration with automatic casting (e.g., `string` to `DateTimeImmutable`)
- OpenAPI configs cleaned: `x-target`/`x-source` hacks replaced with operation-level `x-map` and `x-auth` directives

## Architecture Changes

### New Config Format

```php
'/v1/plays/sessions/{id}' => [
    'patch' => [
        'x-message' => CloseSession\Command::class,
        'x-interceptors' => [AuthInterceptor::class],
        'x-auth' => ['userId'],           // injected from auth context
        'x-map' => ['id' => 'sessionId'], // explicit renames only
        // clean OpenAPI schema -- no x-target, no x-source
        'requestBody' => [...],
        'parameters' => [...],
    ],
],
```

### New Pipeline Flow

1. `CompiledRouteMap::match(method, path)` returns `MatchResult` (operation + pathParams)
2. `InterceptorPipeline::process(request, interceptors)` (unchanged)
3. `OpenApiRequestValidator::validate(request, openApiSchema, pathParams)` (unchanged)
4. `HydratorMapper::map(request, pathParams, authParams, paramMap)` collects all data
5. `ObjectMapper::hydrateObject(messageClass, data)` creates typed Message
6. `Dispatcher::dispatch(message)` (unchanged)

### Key Classes

| Class | Purpose |
|-------|---------|
| `CompiledRouteMap` | O(1) static + compiled regex dynamic route matching |
| `CompiledOperation` | Immutable VO holding message class, interceptors, auth params, param map, schema |
| `MatchResult` | Immutable VO holding matched operation + extracted path params |
| `HydratorMapper` | Collects body/query/path/auth params into flat array for hydration |

### Dependencies Added

- `eventsauce/object-hydrator` ^1.8 -- object hydration with type casting
- Configured with `KeyFormatterWithoutConversion` (keys match constructor params 1:1, no snake_case conversion)

## Removed

- `RouteMap` -- replaced by `CompiledRouteMap`
- `MatchedOperation` -- replaced by `CompiledOperation` + `MatchResult`
- `OpenApiSchemaMapper` -- replaced by `HydratorMapper`
- `x-target`, `x-source` directives in OpenAPI configs -- replaced by `x-map`, `x-auth`
