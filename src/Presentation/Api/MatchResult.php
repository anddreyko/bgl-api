<?php

declare(strict_types=1);

namespace Bgl\Presentation\Api;

final readonly class MatchResult
{
    /**
     * @param array<string, string> $pathParams
     */
    public function __construct(
        public CompiledOperation $operation,
        public array $pathParams = [],
    ) {
    }
}
