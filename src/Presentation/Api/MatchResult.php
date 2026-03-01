<?php

declare(strict_types=1);

namespace Bgl\Presentation\Api;

use Bgl\Core\Http\PathParams;

final readonly class MatchResult
{
    public function __construct(
        public CompiledOperation $operation,
        public PathParams $pathParams = new PathParams(),
    ) {
    }
}
