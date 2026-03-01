<?php

declare(strict_types=1);

namespace Bgl\Core\Http;

use Bgl\Core\Serialization\SerializedData;
use Psr\Http\Message\ServerRequestInterface;

interface SchemaMapper
{
    public function map(
        ServerRequestInterface $request,
        PathParams $pathParams = new PathParams(),
        AuthParams $authParams = new AuthParams(),
        ParamMap $paramMap = new ParamMap(),
    ): SerializedData;
}
