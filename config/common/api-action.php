<?php

declare(strict_types=1);

use Bgl\Core\Http\SchemaMapper;
use Bgl\Core\Messages\Dispatcher;
use Bgl\Core\Serialization\Serializer;
use Bgl\Presentation\Api\ApiAction;
use Bgl\Presentation\Api\InterceptorPipeline;
use Bgl\Presentation\Api\RouteMap;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Slim\Factory\AppFactory;

return [
    ResponseFactoryInterface::class => static function (): ResponseFactoryInterface {
        return AppFactory::determineResponseFactory();
    },
    RouteMap::class => static function (ContainerInterface $container): RouteMap {
        /** @var array{paths: array<string, array<string, mixed>>} $config */
        $config = $container->get('openapi');

        return new RouteMap($config['paths'] ?? []);
    },
    InterceptorPipeline::class => static function (ContainerInterface $container): InterceptorPipeline {
        return new InterceptorPipeline($container);
    },
    ApiAction::class => static function (ContainerInterface $container): ApiAction {
        /** @var RouteMap $routeMap */
        $routeMap = $container->get(RouteMap::class);
        /** @var InterceptorPipeline $interceptorPipeline */
        $interceptorPipeline = $container->get(InterceptorPipeline::class);
        /** @var SchemaMapper $schemaMapper */
        $schemaMapper = $container->get(SchemaMapper::class);
        /** @var Dispatcher $dispatcher */
        $dispatcher = $container->get(Dispatcher::class);
        /** @var Serializer $serializer */
        $serializer = $container->get(Serializer::class);
        /** @var ResponseFactoryInterface $responseFactory */
        $responseFactory = $container->get(ResponseFactoryInterface::class);

        return new ApiAction(
            routeMap: $routeMap,
            interceptorPipeline: $interceptorPipeline,
            schemaMapper: $schemaMapper,
            dispatcher: $dispatcher,
            serializer: $serializer,
            responseFactory: $responseFactory,
            debugMode: (bool) ($_ENV['APP_DEBUG'] ?? false),
        );
    },
];
