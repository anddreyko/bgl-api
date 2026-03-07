<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Http;

/**
 * Recursively adds additionalProperties: false to all object schemas with properties in requestBody.
 */
final class OpenApiSchemaEnforcer
{
    /**
     * @param array<string, array<string, mixed>> $paths
     * @return array<string, array<string, mixed>>
     */
    public static function denyAdditionalPropertiesInRequests(array $paths): array
    {
        foreach ($paths as $pathKey => $methods) {
            /** @var mixed $operationRaw */
            foreach ($methods as $method => $operationRaw) {
                if (!is_array($operationRaw)) {
                    continue;
                }

                /** @var array<string, mixed> $operationRaw */
                $enforced = self::enforceRequestBody($operationRaw);
                if ($enforced !== null) {
                    $paths[$pathKey][$method] = $enforced;
                }
            }
        }

        return $paths;
    }

    /**
     * @param array<string, mixed> $operation
     * @return array<string, mixed>|null
     */
    private static function enforceRequestBody(array $operation): ?array
    {
        if (!isset($operation['requestBody']) || !is_array($operation['requestBody'])) {
            return null;
        }

        /** @var array<string, mixed> $requestBody */
        $requestBody = $operation['requestBody'];

        if (!isset($requestBody['content']) || !is_array($requestBody['content'])) {
            return null;
        }

        /** @var array<string, array<string, mixed>> $content */
        $content = $requestBody['content'];

        foreach ($content as $mediaType => $mediaSpec) {
            if (isset($mediaSpec['schema']) && is_array($mediaSpec['schema'])) {
                /** @var array<string, mixed> $schema */
                $schema = $mediaSpec['schema'];
                $content[$mediaType]['schema'] = self::enforceSchema($schema);
            }
        }

        $requestBody['content'] = $content;
        $operation['requestBody'] = $requestBody;

        return $operation;
    }

    /**
     * @param array<string, mixed> $schema
     * @return array<string, mixed>
     */
    private static function enforceSchema(array $schema): array
    {
        if (($schema['type'] ?? null) === 'object' && isset($schema['properties'])) {
            $schema['additionalProperties'] = false;
        }

        if (isset($schema['properties']) && is_array($schema['properties'])) {
            /** @var array<string, mixed> $properties */
            $properties = $schema['properties'];

            /** @var mixed $propRaw */
            foreach ($properties as $key => $propRaw) {
                if (is_array($propRaw)) {
                    /** @var array<string, mixed> $propRaw */
                    $properties[$key] = self::enforceSchema($propRaw);
                }
            }

            $schema['properties'] = $properties;
        }

        if (isset($schema['items']) && is_array($schema['items'])) {
            /** @var array<string, mixed> $items */
            $items = $schema['items'];
            $schema['items'] = self::enforceSchema($items);
        }

        return $schema;
    }
}
