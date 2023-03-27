<?php

declare(strict_types=1);

namespace App\Http\Actions;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class SwaggerAction implements RequestHandlerInterface
{
    public function __construct(private readonly ResponseFactoryInterface $factory)
    {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $response = $this->factory->createResponse();
        $response->getBody()->write(file_get_contents(__DIR__ . '/../../../web/swagger-ui.html'));

        return $response;
    }
}
