<?php

declare(strict_types=1);

namespace Bgl\Core\Http;

use Bgl\Core\Validation\ValidationErrors;
use Psr\Http\Message\ServerRequestInterface;

interface RequestValidator
{
    /**
     * Validate request against OpenAPI specification.
     *
     * @param ServerRequestInterface $request The incoming HTTP request
     *
     * @return ValidationErrors Field-level validation errors (empty if valid)
     */
    public function validate(ServerRequestInterface $request): ValidationErrors;
}
