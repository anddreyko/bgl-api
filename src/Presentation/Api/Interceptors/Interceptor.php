<?php

declare(strict_types=1);

namespace Bgl\Presentation\Api\Interceptors;

use Psr\Http\Message\ServerRequestInterface;

interface Interceptor
{
    public function process(ServerRequestInterface $request): ServerRequestInterface;
}
