<?php

declare(strict_types=1);

namespace Bgl\Presentation\Api;

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
                    /** @var list<string> $paramNames */
                    $paramNames = [];
                    $regex = preg_replace_callback(
                        '/\{([^}]+)}/',
                        static function (array $m) use (&$paramNames): string {
                            $paramNames[] = $m[1];

                            return '([^/]+)';
                        },
                        $pattern,
                    );
                    if (!is_string($regex)) {
                        continue;
                    }
                    $mark = 'r' . $markIndex++;
                    $dynamicEntries[] = $methodUpper . ' ' . $regex . '(*MARK:' . $mark . ')';
                    $dynamicMap[$mark] = ['operation' => $compiledOp, 'paramNames' => $paramNames];
                }
            }
        }

        $this->staticMap = $staticMap;
        $this->dynamicRegex = $dynamicEntries !== []
            ? '~^(?|' . implode('|', $dynamicEntries) . ')$~'
            : '~^$~';
        $this->dynamicMap = $dynamicMap;
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

                return new MatchResult($entry['operation'], $params);
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
            authParams: $authParams,
            paramMap: $paramMap,
            openApiSchema: $operation,
        );
    }
}
