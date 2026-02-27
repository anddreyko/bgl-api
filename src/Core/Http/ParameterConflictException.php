<?php

declare(strict_types=1);

namespace Bgl\Core\Http;

final class ParameterConflictException extends \RuntimeException
{
    public static function fromPath(string $key): self
    {
        return new self("Path parameter '{$key}' conflicts with body or query parameter");
    }

    public static function fromAuth(string $key): self
    {
        return new self("Auth parameter '{$key}' conflicts with body, query, or path parameter");
    }
}
