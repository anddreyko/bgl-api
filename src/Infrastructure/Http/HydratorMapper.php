<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Http;

use Bgl\Core\Http\ParameterConflictException;
use Bgl\Core\Http\SchemaMapper;
use Psr\Http\Message\ServerRequestInterface;

final readonly class HydratorMapper implements SchemaMapper
{
    #[\Override]
    public function map(
        ServerRequestInterface $request,
        array $pathParams = [],
        array $authParams = [],
        array $paramMap = [],
    ): array {
        /** @var array<string, mixed> $data */
        $data = [];

        // 1. Body params
        /** @var array<string, mixed> $body */
        $body = (array)($request->getParsedBody() ?? []);
        $data = array_merge($data, $body);

        // 2. Query params
        /** @var array<string, string> $queryParams */
        $queryParams = $request->getQueryParams();
        $data = array_merge($data, $queryParams);

        // 3. Path params with x-map renames
        foreach ($pathParams as $key => $value) {
            $mappedKey = $paramMap[$key] ?? $key;
            if (array_key_exists($mappedKey, $data)) {
                throw ParameterConflictException::fromPath($mappedKey);
            }
            $data[$mappedKey] = $value;
        }

        // 4. Auth params from request attributes
        foreach ($authParams as $paramName) {
            /** @var string|null $attrValue */
            $attrValue = $request->getAttribute('auth.' . $paramName);
            if ($attrValue !== null) {
                if (array_key_exists($paramName, $data)) {
                    throw ParameterConflictException::fromAuth($paramName);
                }
                $data[$paramName] = $attrValue;
            }
        }

        return $data;
    }
}
