<?php

declare(strict_types=1);

use Bgl\Core\Http\RequestValidator;
use Bgl\Core\Http\SchemaMapper;
use Bgl\Core\Messages\Dispatcher;
use Bgl\Core\Serialization\Serializer;
use Bgl\Core\ValueObjects\DateTime;
use Bgl\Core\ValueObjects\Uuid;
use Bgl\Infrastructure\Serialization\CastToDateTime;
use Bgl\Infrastructure\Serialization\CastToUuid;
use Bgl\Presentation\Api\ApiAction;
use Bgl\Presentation\Api\CompiledRouteMap;
use Bgl\Presentation\Api\InterceptorPipeline;
use EventSauce\ObjectHydrator\DefaultCasterRepository;
use EventSauce\ObjectHydrator\DefinitionProvider;
use EventSauce\ObjectHydrator\KeyFormatterWithoutConversion;
use EventSauce\ObjectHydrator\ObjectMapper;
use EventSauce\ObjectHydrator\ObjectMapperUsingReflection;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Slim\Factory\AppFactory;

return [
    ResponseFactoryInterface::class => AppFactory::determineResponseFactory(...),
    CompiledRouteMap::class => static function (ContainerInterface $container): CompiledRouteMap {
        /** @var array{paths: array<string, array<string, mixed>>} $config */
        $config = $container->get('openapi');

        return new CompiledRouteMap($config['paths'] ?? []);
    },
    InterceptorPipeline::class => static fn(ContainerInterface $container): InterceptorPipeline
        => new InterceptorPipeline($container),
    ObjectMapper::class => static function (): ObjectMapper {
        $casters = DefaultCasterRepository::builtIn();
        $casters->registerDefaultCaster(Uuid::class, CastToUuid::class);
        $casters->registerDefaultCaster(DateTime::class, CastToDateTime::class);

        return new ObjectMapperUsingReflection(
            new DefinitionProvider(
                defaultCasterRepository: $casters,
                keyFormatter: new KeyFormatterWithoutConversion(),
            ),
        );
    },
    ApiAction::class => static function (ContainerInterface $container): ApiAction {
        /** @var CompiledRouteMap $routeMap */
        $routeMap = $container->get(CompiledRouteMap::class);
        /** @var InterceptorPipeline $interceptorPipeline */
        $interceptorPipeline = $container->get(InterceptorPipeline::class);
        /** @var RequestValidator $requestValidator */
        $requestValidator = $container->get(RequestValidator::class);
        /** @var SchemaMapper $schemaMapper */
        $schemaMapper = $container->get(SchemaMapper::class);
        /** @var ObjectMapper $hydrator */
        $hydrator = $container->get(ObjectMapper::class);
        /** @var Dispatcher $dispatcher */
        $dispatcher = $container->get(Dispatcher::class);
        /** @var Serializer $serializer */
        $serializer = $container->get(Serializer::class);
        /** @var ResponseFactoryInterface $responseFactory */
        $responseFactory = $container->get(ResponseFactoryInterface::class);

        return new ApiAction(
            routeMap: $routeMap,
            interceptorPipeline: $interceptorPipeline,
            requestValidator: $requestValidator,
            schemaMapper: $schemaMapper,
            hydrator: $hydrator,
            dispatcher: $dispatcher,
            serializer: $serializer,
            responseFactory: $responseFactory,
            debugMode: getenv('APP_DEBUG') === 'true' || getenv('APP_DEBUG') === '1',
        );
    },
];
