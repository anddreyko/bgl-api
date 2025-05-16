<?php

declare(strict_types=1);

use App\Application\Handlers\LogErrorHandler;
use App\Application\Handlers\SentryHandler;
use App\Infrastructure\Http\Renderers\JsonErrorRenderer;
use DI\Container;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Log\LoggerInterface;
use Slim\CallableResolver;
use Slim\Interfaces\CallableResolverInterface;
use Slim\Middleware\ErrorMiddleware;

return [
    ErrorMiddleware::class => static function (Container $container) {
        /**
         * @var array{
         *     details: bool,
         *     log: bool
         * } $config
         */
        $config = $container->get('errors');
        /** @var CallableResolverInterface $callable */
        $callable = $container->get(CallableResolver::class);
        /** @var ResponseFactoryInterface $response */
        $response = $container->get(ResponseFactoryInterface::class);
        /** @var LoggerInterface $logger */
        $logger = $container->get(LoggerInterface::class);

        $errors = new ErrorMiddleware($callable, $response, $config['details'], $config['log'], true);

        $handler = new LogErrorHandler($callable, $response, $logger);
        $handler->registerErrorRenderer('application/json', JsonErrorRenderer::class);

        $errors->setDefaultErrorHandler(new SentryHandler($handler));

        return $errors;
    },

    'errors' => [
        'details' => (bool)env('APP_DEBUG'),
        'log' => true,
    ],
];
