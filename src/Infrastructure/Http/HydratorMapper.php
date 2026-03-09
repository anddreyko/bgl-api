<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Http;

use Bgl\Core\Auth\AuthenticationException;
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
        /** @var array<string, string> $queryParams */
        $queryParams = $request->getQueryParams();
        /** @var array<string, mixed> $data */
        $data = array_merge($body, $this->castNumericQueryParams($queryParams));

        $this->applyParamMap($data, $paramMap);
        $this->mergePathParams($data, $pathParams, $paramMap);
        $this->mergeAuthParams($data, $request, $authParams);

        return SerializedData::fromArray($data);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function applyParamMap(array &$data, ParamMap $paramMap): void
    {
        foreach ($paramMap as $from => $to) {
            if (array_key_exists($from, $data) && !array_key_exists($to, $data)) {
                /** @var mixed */
                $data[$to] = $data[$from];
                unset($data[$from]);
            }
        }
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
     * @param array<string, string> $params
     * @return array<string, string|int>
     */
    private function castNumericQueryParams(array $params): array
    {
        $result = [];
        foreach ($params as $key => $value) {
            $result[$key] = ctype_digit($value) ? (int)$value : $value;
        }

        return $result;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function mergeAuthParams(array &$data, ServerRequestInterface $request, AuthParams $authParams): void
    {
        $sentinel = new \stdClass();
        foreach ($authParams as $paramName) {
            /** @var mixed $attrValue */
            $attrValue = $request->getAttribute('auth.' . $paramName, $sentinel);
            if ($attrValue === $sentinel) {
                throw new AuthenticationException('Unauthorized');
            }
            if ($attrValue === null) {
                continue;
            }
            if (array_key_exists($paramName, $data)) {
                throw ParameterConflictException::fromAuth($paramName);
            }
            /** @var string */
            $data[$paramName] = $attrValue;
        }
    }
}
