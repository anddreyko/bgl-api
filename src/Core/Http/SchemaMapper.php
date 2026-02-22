<?php

declare(strict_types=1);

namespace Bgl\Core\Http;

use Psr\Http\Message\ServerRequestInterface;

interface SchemaMapper
{
    /**
     * @param array<string, mixed> $schema OpenAPI requestBody schema properties
     * @param array<string, string> $pathParams Path parameters extracted from URL
     *
     * @return array<string, mixed> Mapped data for message constructor
     */
    public function map(ServerRequestInterface $request, array $schema, array $pathParams = []): array;
}
