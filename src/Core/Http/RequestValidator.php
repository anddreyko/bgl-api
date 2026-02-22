<?php

declare(strict_types=1);

namespace Bgl\Core\Http;

use Psr\Http\Message\ServerRequestInterface;

interface RequestValidator
{
    /**
     * Validate request against OpenAPI operation schema.
     *
     * @param ServerRequestInterface $request The incoming HTTP request
     * @param array<string, mixed> $operation The OpenAPI operation definition
     * @param array<string, string> $pathParams Path parameters extracted from URL
     *
     * @return array<string, string[]> Field-level validation errors (empty if valid)
     */
    public function validate(ServerRequestInterface $request, array $operation, array $pathParams = []): array;
}
