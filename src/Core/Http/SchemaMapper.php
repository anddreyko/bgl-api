<?php

declare(strict_types=1);

namespace Bgl\Core\Http;

use Psr\Http\Message\ServerRequestInterface;

interface SchemaMapper
{
    /**
     * @param array<string, string> $pathParams
     * @param list<string> $authParams
     * @param array<string, string> $paramMap
     *
     * @return array<string, mixed>
     */
    public function map(
        ServerRequestInterface $request,
        array $pathParams = [],
        array $authParams = [],
        array $paramMap = [],
    ): array;
}
