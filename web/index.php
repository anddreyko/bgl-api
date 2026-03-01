<?php

declare(strict_types=1);

use Bgl\Presentation\Api\ApiAction;
use Bgl\Presentation\Api\Middleware\TrimStringsMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

// Build DI Container
$container = require __DIR__ . '/../config/container.php';

// Create Slim App
$app = AppFactory::createFromContainer($container);

$app->addRoutingMiddleware();
$app->addErrorMiddleware(
    displayErrorDetails: getenv('APP_DEBUG') === 'true' || getenv('APP_DEBUG') === '1',
    logErrors: true,
    logErrorDetails: true,
);

$app->add(function (ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
    $contentType = $request->getHeaderLine('Content-Type');
    if ($request->getParsedBody() === null && str_contains($contentType, 'application/json')) {
        $body = (string)$request->getBody();
        if ($body !== '') {
            /** @var array<string, mixed>|null $decoded */
            $decoded = json_decode($body, true);
            if (is_array($decoded)) {
                $request = $request->withParsedBody($decoded);
            }
        }
    }

    return $handler->handle($request);
});

$app->add(new TrimStringsMiddleware());

$app->any('/{path:.*}', function (
    ServerRequestInterface $request,
    ResponseInterface $response,
) use ($container): ResponseInterface {
    /** @var ApiAction $action */
    $action = $container->get(ApiAction::class);

    return $action->handle($request);
});

$app->run();
