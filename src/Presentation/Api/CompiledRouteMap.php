<?php

declare(strict_types=1);

namespace Bgl\Presentation\Api;

use Bgl\Core\Http\AuthParams;
use Bgl\Core\Http\ParamMap;
use Bgl\Core\Http\PathParams;

final readonly class CompiledRouteMap
{
    /** @var array<string, CompiledOperation> */
    private array $staticMap;

    /** @var non-empty-string */
    private string $dynamicRegex;

    /** @var array<string, array{operation: CompiledOperation, paramNames: list<string>}> */
    private array $dynamicMap;

    /**
     * @param array<string, array<string, mixed>> $paths OpenAPI paths configuration
     */
    public function __construct(array $paths)
    {
        $staticMap = [];
        $dynamicEntries = [];
        $dynamicMap = [];
        $markIndex = 0;

        foreach ($paths as $pattern => $operations) {
            foreach ($operations as $method => $operation) {
                if (!is_array($operation) || !isset($operation['x-message']) || !is_string($operation['x-message'])) {
                    continue;
                }

                /** @var array<string, mixed> $operationArray */
                $operationArray = $operation;
                $compiledOp = self::compileOperation($operationArray);
                $methodUpper = strtoupper($method);

                if (!str_contains($pattern, '{')) {
                    $staticMap[$methodUpper . ' ' . $pattern] = $compiledOp;
                } else {
                    $dynamic = self::compileDynamicRoute($pattern, $compiledOp, $methodUpper, $markIndex++);
                    if ($dynamic !== null) {
                        $dynamicEntries[] = $dynamic['entry'];
                        $dynamicMap[$dynamic['mark']] = $dynamic['mapping'];
                    }
                }
            }
        }

        $this->staticMap = $staticMap;
        $this->dynamicRegex = $dynamicEntries !== []
            ? '~^(?|' . implode('|', $dynamicEntries) . ')$~'
            : '~^$~';
        $this->dynamicMap = $dynamicMap;
    }

    /**
     * @return array{entry: string, mark: string, mapping: array{operation: CompiledOperation, paramNames:
     *     list<string>}}|null
     */
    private static function compileDynamicRoute(
        string $pattern,
        CompiledOperation $op,
        string $method,
        int $markIndex
    ): ?array {
        $segments = preg_split('/(\{[^}]+})/', $pattern, -1, PREG_SPLIT_DELIM_CAPTURE);
        if (!is_array($segments)) {
            return null;
        }

        /** @var list<string> $paramNames */
        $paramNames = [];
        $regex = '';
        foreach ($segments as $segment) {
            if (str_starts_with($segment, '{') && str_ends_with($segment, '}')) {
                $paramNames[] = substr($segment, 1, -1);
                $regex .= '([^/]+)';
            } else {
                $regex .= preg_quote($segment, '~');
            }
        }

        $mark = 'r' . $markIndex;

        return [
            'entry' => $method . ' ' . $regex . '(*MARK:' . $mark . ')',
            'mark' => $mark,
            'mapping' => ['operation' => $op, 'paramNames' => $paramNames],
        ];
    }

    public function match(string $method, string $path): ?MatchResult
    {
        $method = strtoupper($method);
        $key = $method . ' ' . $path;

        if (isset($this->staticMap[$key])) {
            return new MatchResult($this->staticMap[$key]);
        }

        $subject = $method . ' ' . $path;
        if (preg_match($this->dynamicRegex, $subject, $matches) === 1 && isset($matches['MARK'])) {
            $mark = $matches['MARK'];
            if (isset($this->dynamicMap[$mark])) {
                $entry = $this->dynamicMap[$mark];
                $params = [];
                foreach ($entry['paramNames'] as $i => $name) {
                    $params[$name] = $matches[$i + 1] ?? '';
                }

                return new MatchResult($entry['operation'], new PathParams($params));
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $operation
     */
    private static function compileOperation(array $operation): CompiledOperation
    {
        /** @var class-string<\Bgl\Core\Messages\Message> $messageClass */
        $messageClass = $operation['x-message'];

        /** @var list<class-string<\Bgl\Presentation\Api\Interceptors\Interceptor>> $interceptors */
        $interceptors = isset($operation['x-interceptors']) && is_array($operation['x-interceptors'])
            ? $operation['x-interceptors']
            : [];

        /** @var list<string> $authParams */
        $authParams = isset($operation['x-auth']) && is_array($operation['x-auth'])
            ? $operation['x-auth']
            : [];

        /** @var array<string, string> $paramMap */
        $paramMap = isset($operation['x-map']) && is_array($operation['x-map'])
            ? $operation['x-map']
            : [];

        return new CompiledOperation(
            messageClass: $messageClass,
            interceptors: $interceptors,
            authParams: new AuthParams($authParams),
            paramMap: new ParamMap($paramMap),
            openApiSchema: $operation,
            successCode: self::resolveSuccessCode($operation),
        );
    }

    /**
     * @param array<string, mixed> $operation
     */
    private static function resolveSuccessCode(array $operation): HttpCode
    {
        if (isset($operation['responses']) && is_array($operation['responses'])) {
            foreach (array_keys($operation['responses']) as $code) {
                $httpCode = HttpCode::tryFrom((int)$code);
                if (
                    $httpCode !== null &&
                    $httpCode->value >= HttpCode::Ok->value &&
                    $httpCode->value < HttpCode::MultipleChoices->value
                ) {
                    return $httpCode;
                }
            }
        }

        return HttpCode::Ok;
    }
}
