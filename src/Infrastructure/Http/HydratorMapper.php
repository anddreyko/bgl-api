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
        /** @var array<string, mixed> $body */
        $body = (array)($request->getParsedBody() ?? []);
        /** @var array<string, mixed> $data */
        $data = array_merge($body, $request->getQueryParams());

        $this->mergePathParams($data, $pathParams, $paramMap);
        $this->mergeAuthParams($data, $request, $authParams);

        return $data;
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, string> $pathParams
     * @param array<string, string> $paramMap
     */
    private function mergePathParams(array &$data, array $pathParams, array $paramMap): void
    {
        foreach ($pathParams as $key => $value) {
            $mappedKey = $paramMap[$key] ?? $key;
            if (array_key_exists($mappedKey, $data)) {
                throw ParameterConflictException::fromPath($mappedKey);
            }
            $data[$mappedKey] = $value;
        }
    }

    /**
     * @param array<string, mixed> $data
     * @param list<string> $authParams
     */
    private function mergeAuthParams(array &$data, ServerRequestInterface $request, array $authParams): void
    {
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
    }
}
