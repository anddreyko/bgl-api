<?php

declare(strict_types=1);

namespace Bgl\Presentation\Api;

final readonly class RouteMap
{
    /**
     * @param array<string, array<string, mixed>> $paths OpenAPI paths configuration
     */
    public function __construct(
        private array $paths,
    ) {
    }

    public function match(string $method, string $path): ?MatchedOperation
    {
        $method = strtolower($method);

        foreach ($this->paths as $pattern => $operations) {
            $pathParams = $this->matchPath($pattern, $path);

            if ($pathParams === null) {
                continue;
            }

            if (!isset($operations[$method]) || !is_array($operations[$method])) {
                continue;
            }

            /** @var array<string, mixed> $operation */
            $operation = $operations[$method];

            if (!isset($operation['x-message']) || !is_string($operation['x-message'])) {
                continue;
            }

            /** @var class-string<\Bgl\Core\Messages\Message> $messageClass */
            $messageClass = $operation['x-message'];

            /** @var list<class-string<\Bgl\Presentation\Api\Interceptors\Interceptor>> $interceptors */
            $interceptors = isset($operation['x-interceptors']) && is_array($operation['x-interceptors'])
                ? $operation['x-interceptors']
                : [];

            $schema = self::extractSchema($operation);

            return new MatchedOperation(
                messageClass: $messageClass,
                interceptors: $interceptors,
                pathParams: $pathParams,
                schema: $schema,
            );
        }

        return null;
    }

    /**
     * @return array<string, string>|null Path params if matched, null otherwise
     */
    private function matchPath(string $pattern, string $path): ?array
    {
        /** @var list<string> $paramNames */
        $paramNames = [];
        $regex = preg_replace_callback(
            '/\{([^}]+)}/',
            static function (array $matches) use (&$paramNames): string {
                $paramNames[] = $matches[1];
                return '([^/]+)';
            },
            $pattern,
        );

        if (!is_string($regex)) {
            return null;
        }

        $regex = '#^' . $regex . '$#';

        if (preg_match($regex, $path, $matches) !== 1) {
            return null;
        }

        $params = [];
        foreach ($paramNames as $index => $name) {
            $params[$name] = $matches[$index + 1];
        }

        return $params;
    }

    /**
     * @param array<string, mixed> $operation
     *
     * @return array<string, mixed>
     */
    private static function extractSchema(array $operation): array
    {
        /** @var array<string, mixed> $schema */
        $schema = [];

        /** @var mixed $requestBody */
        $requestBody = $operation['requestBody'] ?? null;
        if (is_array($requestBody)) {
            /** @var mixed $content */
            $content = $requestBody['content'] ?? null;
            if (is_array($content)) {
                /** @var mixed $jsonContent */
                $jsonContent = $content['application/json'] ?? null;
                if (is_array($jsonContent)) {
                    /** @var mixed $schemaNode */
                    $schemaNode = $jsonContent['schema'] ?? null;
                    if (is_array($schemaNode)) {
                        /** @var mixed $properties */
                        $properties = $schemaNode['properties'] ?? null;
                        if (is_array($properties)) {
                            /** @var array<string, mixed> $properties */
                            $schema = $properties;
                        }
                    }
                }
            }
        }

        /** @var mixed $parameters */
        $parameters = $operation['parameters'] ?? null;
        if (is_array($parameters)) {
            foreach ($parameters as $param) {
                if (!is_array($param)) {
                    continue;
                }
                /** @var mixed $in */
                $in = $param['in'] ?? '';
                /** @var mixed $name */
                $name = $param['name'] ?? null;
                if ($in === 'query' && is_string($name)) {
                    /** @var array<string, mixed>|array<never, never> $paramSchema */
                    $paramSchema = is_array($param['schema'] ?? null) ? $param['schema'] : [];
                    $schema[$name] = $paramSchema;
                }
            }
        }

        return $schema;
    }
}
