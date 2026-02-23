<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Http;

use Bgl\Core\Http\SchemaMapper;
use Psr\Http\Message\ServerRequestInterface;

final readonly class OpenApiSchemaMapper implements SchemaMapper
{
    #[\Override]
    public function map(ServerRequestInterface $request, array $schema, array $pathParams = []): array
    {
        if ($schema === []) {
            return [];
        }

        /** @var array<string, mixed> $body */
        $body = (array) ($request->getParsedBody() ?? []);
        /** @var array<string, string> $queryParams */
        $queryParams = $request->getQueryParams();

        $result = [];

        /**
         * @var string $propertyName
         * @var mixed $propertyConfig
         */
        foreach ($schema as $propertyName => $propertyConfig) {
            $result = $this->processProperty(
                $propertyName,
                $propertyConfig,
                $pathParams,
                $body,
                $queryParams,
                $result,
                $request,
            );
        }

        return $result;
    }

    /**
     * @param array<string, string> $pathParams
     * @param array<string, mixed> $body
     * @param array<string, string> $queryParams
     * @param array<string, mixed> $result
     *
     * @return array<string, mixed>
     */
    private function processProperty(
        string $propertyName,
        mixed $propertyConfig,
        array $pathParams,
        array $body,
        array $queryParams,
        array $result,
        ServerRequestInterface $request,
    ): array {
        $targetName = $propertyName;
        $cast = null;

        if (is_array($propertyConfig) && isset($propertyConfig['x-target']) && is_string($propertyConfig['x-target'])) {
            $parts = explode('|', $propertyConfig['x-target']);
            $targetName = $parts[0];
            $cast = $parts[1] ?? null;
        }

        if (is_array($propertyConfig) && isset($propertyConfig['x-source']) && is_string($propertyConfig['x-source'])) {
            $source = $propertyConfig['x-source'];
            if (str_starts_with($source, 'attribute:')) {
                $attrName = substr($source, 10);
                /** @var string|null $attrValue */
                $attrValue = $request->getAttribute($attrName);
                if ($attrValue !== null) {
                    $result[$targetName] = $attrValue;

                    return $result;
                }
            }
        }

        $value = $this->resolveValue($propertyName, $pathParams, $body, $queryParams);

        if ($value === null) {
            return $result;
        }

        /** @var string|int|float|bool|array<mixed>|\DateTimeImmutable $mapped */
        $mapped = $cast !== null ? self::castValue($value, $cast) : $value;
        $result[$targetName] = $mapped;

        return $result;
    }

    /**
     * @param array<string, string> $pathParams
     * @param array<string, mixed> $body
     * @param array<string, string> $queryParams
     *
     * @return string|int|float|bool|array<mixed>|null
     */
    private function resolveValue(
        string $propertyName,
        array $pathParams,
        array $body,
        array $queryParams,
    ): string|int|float|bool|array|null {
        if (array_key_exists($propertyName, $pathParams)) {
            return $pathParams[$propertyName];
        }

        if (array_key_exists($propertyName, $body)) {
            /** @var string|int|float|bool|array<mixed>|null */
            return $body[$propertyName];
        }

        if (array_key_exists($propertyName, $queryParams)) {
            return $queryParams[$propertyName];
        }

        return null;
    }

    /**
     * @param string|int|float|bool|array<mixed> $value
     *
     * @return string|int|float|bool|array<mixed>|\DateTimeImmutable
     */
    private static function castValue(
        string|int|float|bool|array $value,
        string $cast,
    ): string|int|float|bool|array|\DateTimeImmutable {
        if (is_array($value)) {
            return $value;
        }

        return match ($cast) {
            'int' => (int) $value,
            'bool' => (bool) $value,
            'float' => (float) $value,
            'datetime' => new \DateTimeImmutable((string) $value),
            default => $value,
        };
    }
}
