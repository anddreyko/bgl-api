# Unified Route Configuration as Single Source of Truth

## Date: 2026-01-05

## Authors: BoardGameLog Team

## Status: Accepted

---

### Context

BoardGameLog API requires consistency across multiple concerns that traditionally use separate configuration sources:

- **Routing** — mapping HTTP endpoints to handlers
- **OpenAPI generation** — API documentation for frontend consumers
- **Request validation** — validating incoming payloads against schemas
- **Response serialization** — transforming domain objects to API responses
- **Message mapping** — connecting HTTP requests to Command/Query messages

Currently, these concerns are often defined separately, leading to:

- Duplication of endpoint definitions across routing, OpenAPI specs, and validation rules
- Drift between documentation and actual implementation
- Inconsistent validation between OpenAPI schema and runtime checks
- Manual synchronization overhead when changing API structure
- Scattered configuration that makes API evolution difficult

**Requirements:**

- Single configuration file per route defining all aspects
- Schema-based structure (similar to OpenAPI/JSON Schema)
- Additional fields for interceptors (aspects/middleware)
- Message class mapping for CQS pattern integration
- Code generation capability for OpenAPI export
- Runtime validation using the same schema
- Type-safe serialization configuration

---

### Considered Options

#### Option 1: Attribute-Based Configuration

Use PHP attributes on Controller/Action classes to define routing, validation, and OpenAPI metadata.

**Pros:**

- Co-located with handler code
- IDE support and refactoring safety
- Type-safe in PHP context

**Cons:**

- Scattered across multiple files
- Cannot generate OpenAPI without loading PHP classes
- Difficult to get overview of entire API surface
- Mixing infrastructure concerns with presentation layer
- Attributes become verbose with all metadata

#### Option 2: Separate Configuration Files

Maintain separate files: routes.php, openapi.yaml, validation rules, serializer config.

**Pros:**

- Standard approach, familiar tooling
- Each concern has dedicated format

**Cons:**

- Synchronization nightmare
- Documentation drift from implementation
- Multiple sources of truth
- High maintenance overhead
- Changes require updates in multiple places

#### Option 3: Unified Route Configuration (Schema-First)

Single configuration source per route/resource combining all concerns in a schema-like format with extensions.

**Pros:**

- Single source of truth eliminates drift
- Schema structure familiar from OpenAPI/JSON Schema
- Extensible with custom fields (interceptors, messages)
- Can generate OpenAPI documentation automatically
- Runtime validation uses same schema definition
- Clear API contract in one place
- Easy to review and audit entire API surface

**Cons:**

- Custom format requires tooling investment
- Learning curve for team
- Need to build generators/validators

---

### Decision

**Decision:** Use Unified Route Configuration as single source of truth

**Reason for choice:**

1. **Eliminates duplication** — one definition serves routing, validation, OpenAPI, and serialization
2. **Prevents drift** — documentation always matches implementation
3. **Schema-first design** — aligns with API-first development practices
4. **Extensibility** — custom fields for interceptors and message mapping integrate naturally
5. **Auditability** — entire API surface visible in configuration files
6. **Code generation** — enables automated OpenAPI export for frontend

---

### Configuration Structure

Configuration follows OpenAPI 3.0 specification structure as PHP arrays. Custom extensions use `x-` prefix.

#### Main Configuration

```php
// config/openapi.php
return [
    'openapi' => '3.0.0',
    'info' => [
        'title' => 'BoardGameLog API',
        'version' => '1.0.0',
        'description' => 'API for tracking board game sessions',
    ],
    'servers' => [
        ['url' => 'http://localhost:8080', 'description' => 'Development'],
    ],
    'paths' => array_merge(
        require __DIR__ . '/openapi/plays.php',
        require __DIR__ . '/openapi/auth.php',
        require __DIR__ . '/openapi/games.php',
    ),
    'components' => require __DIR__ . '/openapi/components.php',
];
```

#### Route Configuration Example

```php
// config/openapi/plays.php
return [
    '/v1/plays' => [
        'post' => [
            'operationId' => 'plays.create',
            'summary' => 'Create a new play session',
            'tags' => ['Plays'],

            // Custom extensions for routing/middleware
            'x-message' => CreatePlayCommand::class,
            'x-interceptors' => [
                Authenticated::class,
                RateLimited::class => ['limit' => 100, 'window' => 3600],
            ],

            'requestBody' => [
                'required' => true,
                'content' => [
                    'application/json' => [
                        'schema' => ['$ref' => '#/components/schemas/CreatePlayRequest'],
                    ],
                ],
            ],

            'responses' => [
                '201' => [
                    'description' => 'Play session created',
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['code', 'data'],
                                'properties' => [
                                    'code' => ['type' => 'integer', 'example' => 0],
                                    'data' => ['$ref' => '#/components/schemas/Play'],
                                ],
                            ],
                        ],
                    ],
                ],
                '400' => [
                    'description' => 'Validation error',
                    'content' => [
                        'application/json' => [
                            'schema' => ['$ref' => '#/components/schemas/ErrorResponse'],
                        ],
                    ],
                ],
                '401' => [
                    'description' => 'Unauthorized',
                    'content' => [
                        'application/json' => [
                            'schema' => ['$ref' => '#/components/schemas/ErrorResponse'],
                        ],
                    ],
                ],
            ],
        ],

        'get' => [
            'operationId' => 'plays.list',
            'summary' => 'List play sessions',
            'tags' => ['Plays'],
            'x-message' => ListPlaysQuery::class,
            'x-interceptors' => [Authenticated::class],

            'parameters' => [
                [
                    'name' => 'page',
                    'in' => 'query',
                    'schema' => ['type' => 'integer', 'minimum' => 1, 'default' => 1],
                ],
                [
                    'name' => 'perPage',
                    'in' => 'query',
                    'schema' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 100, 'default' => 20],
                ],
                [
                    'name' => 'gameId',
                    'in' => 'query',
                    'schema' => ['type' => 'string', 'format' => 'uuid'],
                ],
                [
                    'name' => 'status',
                    'in' => 'query',
                    'schema' => ['type' => 'string', 'enum' => ['active', 'completed']],
                ],
            ],

            'responses' => [
                '200' => [
                    'description' => 'List of play sessions',
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['code', 'data'],
                                'properties' => [
                                    'code' => ['type' => 'integer', 'example' => 0],
                                    'data' => [
                                        'type' => 'array',
                                        'items' => ['$ref' => '#/components/schemas/Play'],
                                    ],
                                    'pagination' => ['$ref' => '#/components/schemas/Pagination'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],

    '/v1/plays/{id}' => [
        'get' => [
            'operationId' => 'plays.show',
            'summary' => 'Get play session details',
            'tags' => ['Plays'],
            'x-message' => GetPlayQuery::class,
            'x-interceptors' => [Authenticated::class],

            'parameters' => [
                [
                    'name' => 'id',
                    'in' => 'path',
                    'required' => true,
                    'schema' => ['type' => 'string', 'format' => 'uuid'],
                ],
            ],

            'responses' => [
                '200' => [
                    'description' => 'Play session details',
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['code', 'data'],
                                'properties' => [
                                    'code' => ['type' => 'integer', 'example' => 0],
                                    'data' => ['$ref' => '#/components/schemas/Play'],
                                ],
                            ],
                        ],
                    ],
                ],
                '404' => [
                    'description' => 'Not found',
                    'content' => [
                        'application/json' => [
                            'schema' => ['$ref' => '#/components/schemas/ErrorResponse'],
                        ],
                    ],
                ],
            ],
        ],
    ],
];
```

#### Directory Structure

```
config/
├── openapi.php           # Main OpenAPI configuration
└── openapi/
    ├── components.php    # Shared schemas, security schemes
    ├── auth.php          # Auth context paths
    ├── games.php         # Games context paths
    ├── plays.php         # Plays context paths
    └── stats.php         # Stats context paths
```

#### Components (Shared Schemas)

```php
// config/openapi/components.php
return [
    'schemas' => [
        // Response schema with x-source for serialization mapping
        'Play' => [
            'type' => 'object',
            'x-source' => Play::class, // Domain entity for serialization
            'properties' => [
                'id' => [
                    'type' => 'string',
                    'format' => 'uuid',
                    'x-source' => 'id.value', // Path to value in entity
                ],
                'gameId' => [
                    'type' => 'string',
                    'format' => 'uuid',
                    'x-source' => 'game.id.value',
                ],
                'startedAt' => [
                    '$ref' => '#/components/schemas/DateTime',
                    'x-source' => 'startedAt', // Uses DateTime schema
                ],
                'finishedAt' => [
                    'allOf' => [
                        ['$ref' => '#/components/schemas/DateTime'],
                    ],
                    'nullable' => true,
                    'x-source' => 'finishedAt|nullable',
                ],
                'duration' => [
                    '$ref' => '#/components/schemas/DateInterval',
                    'x-source' => 'duration', // Uses DateInterval schema
                ],
                'status' => [
                    'type' => 'string',
                    'enum' => ['active', 'completed'],
                    'x-source' => 'status.value',
                ],
                'location' => [
                    'type' => 'string',
                    'nullable' => true,
                    'x-source' => 'location',
                ],
            ],
        ],

        // Request schema with x-target for hydration mapping
        'CreatePlayRequest' => [
            'type' => 'object',
            'required' => ['gameId'],
            'properties' => [
                'gameId' => [
                    'type' => 'string',
                    'format' => 'uuid',
                    'x-target' => 'gameId', // Maps to message property
                ],
                'startedAt' => [
                    'type' => 'string',
                    'format' => 'date-time',
                    'x-target' => 'startedAt|datetime', // With type cast
                ],
                'location' => [
                    'type' => 'string',
                    'maxLength' => 255,
                    'x-target' => 'location',
                ],
                'players' => [
                    'type' => 'array',
                    'x-target' => 'players',
                    'items' => [
                        'type' => 'object',
                        'required' => ['userId'],
                        'properties' => [
                            'userId' => [
                                'type' => 'string',
                                'format' => 'uuid',
                                'x-target' => 'userId',
                            ],
                            'score' => [
                                'type' => 'integer',
                                'minimum' => 0,
                                'x-target' => 'score|int',
                            ],
                            'winner' => [
                                'type' => 'boolean',
                                'x-target' => 'winner|bool',
                            ],
                        ],
                    ],
                ],
            ],
        ],

        'ErrorResponse' => [
            'type' => 'object',
            'required' => ['code', 'message'],
            'properties' => [
                'code' => ['type' => 'integer', 'example' => 1],
                'message' => ['type' => 'string'],
                'errors' => [
                    'type' => 'object',
                    'additionalProperties' => [
                        'type' => 'object',
                        'additionalProperties' => ['type' => 'string'],
                    ],
                ],
                'exception' => ['$ref' => '#/components/schemas/ExceptionDetails'],
            ],
        ],

        'ExceptionDetails' => [
            'type' => 'object',
            'properties' => [
                'code' => ['type' => 'integer'],
                'message' => ['type' => 'string'],
                'trace' => ['type' => 'string'],
            ],
        ],

        'Pagination' => [
            'type' => 'object',
            'properties' => [
                'total' => ['type' => 'integer'],
                'pages' => ['type' => 'integer'],
                'current' => ['type' => 'integer'],
                'page_size' => ['type' => 'integer'],
            ],
        ],

        // Universal date/time serialization schemas
        'Date' => [
            'type' => 'object',
            'description' => 'Date without time (DateTimeInterface)',
            'x-source-type' => DateTimeInterface::class,
            'properties' => [
                'date' => [
                    'type' => 'string',
                    'format' => 'date',
                    'example' => '2026-01-05',
                    'x-source' => 'format:Y-m-d',
                ],
                'timestamp' => [
                    'type' => 'integer',
                    'example' => 1767657600,
                    'x-source' => 'getTimestamp',
                ],
            ],
        ],

        'DateTime' => [
            'type' => 'object',
            'description' => 'Date with time (DateTimeInterface)',
            'x-source-type' => DateTimeInterface::class,
            'properties' => [
                'date' => [
                    'type' => 'string',
                    'format' => 'date-time',
                    'example' => '2026-01-05T14:30:00+00:00',
                    'x-source' => 'format:' . DATE_RFC3339,
                ],
                'timestamp' => [
                    'type' => 'integer',
                    'example' => 1767709800,
                    'x-source' => 'getTimestamp',
                ],
            ],
        ],

        'DateInterval' => [
            'type' => 'object',
            'description' => 'Duration interval (DateInterval)',
            'x-source-type' => DateInterval::class,
            'properties' => [
                'interval' => [
                    'type' => 'string',
                    'description' => 'ISO 8601 duration format',
                    'example' => 'P1DT2H30M',
                    'x-source' => 'format:P%yY%mM%dDT%hH%iM%sS|trimInterval',
                ],
                'seconds' => [
                    'type' => 'integer',
                    'description' => 'Total seconds',
                    'example' => 95400,
                    'x-source' => 'totalSeconds',
                ],
            ],
        ],
    ],

    'securitySchemes' => [
        'bearerAuth' => [
            'type' => 'http',
            'scheme' => 'bearer',
            'bearerFormat' => 'JWT',
        ],
    ],
];
```

#### Mapping Syntax

**Response serialization (`x-source`):**

```
x-source: propertyPath|pipe1:arg|pipe2
```

- `id.value` — nested property access
- `|date:c` — format as ISO date
- `|nullable` — allow null values
- `|string`, `|int`, `|bool` — type cast

**Request hydration (`x-target`):**

```
x-target: propertyName|cast
```

- Direct mapping to message/array property
- `|datetime` — parse as DateTimeImmutable
- `|int`, `|bool`, `|float` — type cast

#### Custom Extensions (Internal Only)

| Extension        | Location  | Purpose                                          |
|------------------|-----------|--------------------------------------------------|
| `x-message`      | operation | Command/Query class for CQS dispatch             |
| `x-interceptors` | operation | Middleware/aspects for this route                |
| `x-source`       | property  | Path to value in domain object for serialization |
| `x-source-type`  | schema    | PHP class/interface for type detection           |
| `x-target`       | property  | Target property name for request hydration       |

**Security Requirements:**

1. **Strip from public OpenAPI export** — when generating `openapi.yaml` for frontend, all `x-*` fields must be removed.
   Frontend receives clean OpenAPI spec without internal implementation details.

2. **Sanitize incoming requests** — any `x-*` headers or fields in incoming HTTP requests must be ignored/stripped.
   These values come only from route configuration, never from client.

```php
// OpenAPI export (strips x-* extensions)
final class OpenApiExporter
{
    public function export(array $config): array
    {
        return $this->stripExtensions($config);
    }

    private function stripExtensions(array $data): array
    {
        $result = [];
        foreach ($data as $key => $value) {
            if (str_starts_with((string) $key, 'x-')) {
                continue; // Skip internal extensions
            }
            $result[$key] = is_array($value) ? $this->stripExtensions($value) : $value;
        }
        return $result;
    }
}
```

---

### Implementation Components

#### 1. Route Loader

Loads configuration and registers routes with Slim:

```php
final class UnifiedRouteLoader
{
    public function load(App $app, array $config): void
    {
        foreach ($config as $name => $route) {
            $app->{strtolower($route['method'])}(
                $route['path'],
                $this->createHandler($route)
            )->setName($name);
        }
    }
}
```

#### 2. Request Validation (league/openapi-psr7-validator)

Uses `league/openapi-psr7-validator` for PSR-15 compatible request/response validation:

```php
// PSR-15 middleware - validates against OpenAPI schema automatically
use League\OpenAPIValidation\PSR15\ValidationMiddleware;
use League\OpenAPIValidation\PSR15\ValidationMiddlewareBuilder;

$validationMiddleware = (new ValidationMiddlewareBuilder())
    ->fromYamlFile('/path/to/openapi.yaml')
    ->getValidationMiddleware();

// Add to Slim middleware stack
$app->add($validationMiddleware);
```

Benefits:

- Automatic validation of request body, query params, path params, headers
- Automatic response validation (optional, for development)
- No custom validation code needed
- Schema is single source of truth

#### 3. OpenAPI Generator

Generates OpenAPI spec from configuration:

```php
final class OpenApiGenerator
{
    public function generate(array $routes, array $schemas): array
    {
        // Transforms route config to OpenAPI 3.0 format
        // Output can be exported as JSON/YAML for frontend
    }
}
```

#### 4. Schema-Based Request Mapper

Maps request data to array using `x-target` schema mappings (no DTO creation):

```php
final class SchemaRequestMapper
{
    public function map(ServerRequestInterface $request, array $schema): array
    {
        $body = $request->getParsedBody() ?? [];
        $query = $request->getQueryParams();
        $result = [];

        foreach ($schema['properties'] ?? [] as $name => $prop) {
            $target = $prop['x-target'] ?? $name;
            [$targetName, $cast] = $this->parseTarget($target);

            $value = $body[$name] ?? $query[$name] ?? null;
            $result[$targetName] = $this->cast($value, $cast);
        }

        return $result; // Returns plain array, not DTO
    }
}
```

#### 5. Schema-Based Response Serializer

Serializes domain objects using `x-source` schema mappings (no Transformer classes):

```php
final class SchemaResponseSerializer
{
    public function serialize(object $entity, array $schema): array
    {
        $result = [];

        foreach ($schema['properties'] ?? [] as $name => $prop) {
            if (isset($prop['$ref'])) {
                $refSchema = $this->resolveRef($prop['$ref']);
                $result[$name] = $this->serialize(
                    $this->getValue($entity, $prop['x-source'] ?? $name),
                    $refSchema
                );
                continue;
            }

            $source = $prop['x-source'] ?? $name;
            $result[$name] = $this->extractValue($entity, $source);
        }

        return $result;
    }

    private function extractValue(object $entity, string $source): mixed
    {
        // Parse: "startedAt|date:c|nullable"
        [$path, ...$pipes] = explode('|', $source);

        $value = $this->getValue($entity, $path);

        foreach ($pipes as $pipe) {
            $value = $this->applyPipe($value, $pipe);
        }

        return $value;
    }
}
```

---

### Request Processing Flow

```
HTTP Request
     │
     ▼
┌─────────────────────────────────────────────────────┐
│  1. Route Matching (path + method from config)      │
└─────────────────────────────────────────────────────┘
     │
     ▼
┌─────────────────────────────────────────────────────┐
│  2. Interceptors (from x-interceptors)              │
│     - Authentication, Rate Limiting, etc.           │
└─────────────────────────────────────────────────────┘
     │
     ▼
┌─────────────────────────────────────────────────────┐
│  3. Request Validation (against requestBody schema) │
│     - Body, Query, Path parameters                  │
└─────────────────────────────────────────────────────┘
     │
     ▼
┌─────────────────────────────────────────────────────┐
│  4. Request Mapping (x-target -> array)             │
│     - Schema-based, no DTO creation                 │
└─────────────────────────────────────────────────────┘
     │
     ▼
┌─────────────────────────────────────────────────────┐
│  5. MessageBus Dispatch (x-message class)           │
│     - Handler receives mapped array                 │
└─────────────────────────────────────────────────────┘
     │
     ▼
┌─────────────────────────────────────────────────────┐
│  6. Response Serialization (x-source -> JSON)       │
│     - Schema-based, no Transformer classes          │
└─────────────────────────────────────────────────────┘
     │
     ▼
HTTP Response
```

---

### Consequences

**Positive:**

- Single source of truth for entire API contract
- OpenAPI documentation always matches implementation
- Validation rules defined once, used everywhere (via `league/openapi-psr7-validator`)
- Clear visibility into API surface from configuration files
- Simplified API evolution with centralized changes
- Frontend can auto-generate clients from exported OpenAPI
- Route interceptors clearly visible per-endpoint
- No separate Transformer classes — serialization defined in schema
- No DTO classes for requests — mapping defined in schema
- No custom validation code — PSR-15 middleware handles it
- Reduced boilerplate code
- Aligns with League PHP preference (ADR-009)

**Negative/Risks:**

- Initial investment in tooling (schema mapper, serializer)
- Custom `x-*` extensions require documentation
- Complex nested mappings may be harder to debug (mitigated by clear syntax)
- Need to ensure schema stays in sync with domain objects (mitigated by tests)

---

### Notes

**Make commands to add:**

```makefile
openapi:     ## Generate OpenAPI specification (without x-* extensions)
	docker compose run --rm php-cli bin/console api:openapi:generate

routes:      ## List all configured routes
	docker compose run --rm php-cli bin/console api:routes:list
```

**Related ADRs:**

- [ADR-003: Mediator Pattern](./003-mediator-pattern.md) — Message/Command/Query structure
- [ADR-004: Aspects](./004-aspects.md) — Interceptor/middleware pattern
- [ADR-012: Validation Library](./012-validation-library.md) — Validation approach

**Required packages:**

```json
{
    "require": {
        "league/openapi-psr7-validator": "^0.22"
    }
}
```

**References:**

- [OpenAPI Specification 3.0](https://swagger.io/specification/)
- [JSON Schema](https://json-schema.org/)
- [API-First Design](https://swagger.io/resources/articles/adopting-an-api-first-approach/)
- [league/openapi-psr7-validator](https://github.com/thephpleague/openapi-psr7-validator)
