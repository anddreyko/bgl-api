<?php

declare(strict_types=1);

use Bgl\Presentation\Api\ApiAction;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

// Build DI Container
$container = require __DIR__ . '/../config/container.php';

// Create Slim App
$app = AppFactory::createFromContainer($container);

$app->addRoutingMiddleware();
$app->addErrorMiddleware(
    displayErrorDetails: (bool)($_ENV['APP_DEBUG'] ?? false),
    logErrors: true,
    logErrorDetails: true,
);

$app->any('/{path:.*}', function (
    ServerRequestInterface $request,
    ResponseInterface $response,
) use ($container): ResponseInterface {
    /** @var \Bgl\Presentation\Api\ApiAction $action */
    $action = $container->get(ApiAction::class);

    return $action->handle($request);
});

$app->run();
