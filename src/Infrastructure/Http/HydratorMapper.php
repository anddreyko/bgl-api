<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Http;

use Bgl\Core\Http\AuthParams;
use Bgl\Core\Http\ParamMap;
use Bgl\Core\Http\ParameterConflictException;
use Bgl\Core\Http\PathParams;
use Bgl\Core\Http\SchemaMapper;
use Bgl\Core\Serialization\SerializedData;
use Psr\Http\Message\ServerRequestInterface;

final readonly class HydratorMapper implements SchemaMapper
{
    #[\Override]
    public function map(
        ServerRequestInterface $request,
        PathParams $pathParams = new PathParams(),
        AuthParams $authParams = new AuthParams(),
        ParamMap $paramMap = new ParamMap(),
    ): SerializedData {
        /** @var array<string, mixed> $body */
        $body = (array)($request->getParsedBody() ?? []);
        /** @var array<string, mixed> $data */
        $data = array_merge($body, $request->getQueryParams());

        $this->mergePathParams($data, $pathParams, $paramMap);
        $this->mergeAuthParams($data, $request, $authParams);

        return SerializedData::fromArray($data);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function mergePathParams(array &$data, PathParams $pathParams, ParamMap $paramMap): void
    {
        foreach ($pathParams as $key => $value) {
            $mappedKey = $paramMap->get($key) ?? $key;
            if (array_key_exists($mappedKey, $data)) {
                throw ParameterConflictException::fromPath($mappedKey);
            }
            $data[$mappedKey] = $value;
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    private function mergeAuthParams(array &$data, ServerRequestInterface $request, AuthParams $authParams): void
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
