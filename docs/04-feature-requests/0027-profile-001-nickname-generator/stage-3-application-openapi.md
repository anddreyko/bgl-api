# Stage 3: Application Layer + OpenAPI Route

## Overview

Create Query, Handler for the `POST /v1/profile/generate-name` endpoint. Add OpenAPI route config and register handler in the message bus.

## Dependencies

Stage 1 (Nomenclator interface and implementation must exist).

## Implementation Steps

### 3.1 Create Query

File: `src/Application/Handlers/Profile/GenerateNickname/Query.php`

```php
/**
 * @implements Message<string>
 */
final readonly class Query implements Message
{
}
```

No parameters -- just triggers generation. Returns string (the generated name).

### 3.2 Create Handler

File: `src/Application/Handlers/Profile/GenerateNickname/Handler.php`

```php
/**
 * @implements MessageHandler<string, Query>
 */
final readonly class Handler implements MessageHandler
{
    public function __construct(
        private Nomenclator $nomenclator,
    ) {}

    public function __invoke(Envelope $envelope): string
    {
        return $this->nomenclator->generate();
    }
}
```

Returns plain string -- serialized as `StringSuccess` response by ApiAction.

### 3.3 Create OpenAPI route config

File: `config/common/openapi/profile.php`

```php
return [
    'openapi' => [
        'paths' => [
            '/v1/profile/generate-name' => [
                'post' => [
                    'summary' => 'Generate suggested nickname',
                    'operationId' => 'generateNickname',
                    'tags' => ['Profile'],
                    'x-message' => GenerateNickname\Query::class,
                    'responses' => [
                        '200' => ['$ref' => '#/components/responses/StringSuccess'],
                        '500' => ['$ref' => '#/components/responses/InternalError'],
                    ],
                ],
            ],
        ],
    ],
];
```

No auth, no request body, no interceptors.

### 3.4 Register handler in bus

File: `config/common/bus.php`

Add to `'handlers'` array:
```php
[Profile\GenerateNickname\Query::class, Profile\GenerateNickname\Handler::class],
```

Add `use` statement:
```php
use Bgl\Application\Handlers\Profile;
```

### 3.5 Include profile.php in app config

Check how other openapi files (auth.php, user.php, mates.php) are included and follow same pattern.

## Files to Create/Modify

| File | Action |
|------|--------|
| `src/Application/Handlers/Profile/GenerateNickname/Query.php` | CREATE |
| `src/Application/Handlers/Profile/GenerateNickname/Handler.php` | CREATE |
| `config/common/openapi/profile.php` | CREATE |
| `config/common/bus.php` | MODIFY (register handler) |

## Completion Criteria

- `POST /v1/profile/generate-name` route is recognized by CompiledRouteMap
- Handler returns a generated nickname string
- Response format: `{"code": 0, "data": "EpicDice42"}`

## Verification

```bash
composer lp:run
composer ps:run src/Application/Handlers/Profile/GenerateNickname/
```

## Potential Issues

- Need to find where openapi config files are aggregated (likely in app bootstrap or DI config)
- Empty Query class may need special handling if ObjectMapper expects at least one property
