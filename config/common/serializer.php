<?php

declare(strict_types=1);

use Bgl\Core\Serialization\Deserializer;
use Bgl\Core\Serialization\Serializer;
use Bgl\Infrastructure\Serialization\FractalSerializer;
use Bgl\Infrastructure\Serialization\MappedDeserializer;
use EventSauce\ObjectHydrator\ObjectMapper;
use League\Fractal\Manager;
use League\Fractal\Serializer\DataArraySerializer;
use Psr\Container\ContainerInterface;

return [
    FractalSerializer::class => static function (ContainerInterface $container): FractalSerializer {
        /** @var Manager $manager */
        $manager = $container->get(Manager::class);
        $manager->setSerializer(new DataArraySerializer());

        return new FractalSerializer($manager, require __DIR__ . '/../_serialise-mapping.php');
    },
    Serializer::class => DI\get(FractalSerializer::class),
    MappedDeserializer::class => static function (ContainerInterface $container): MappedDeserializer {
        /** @var ObjectMapper $hydrator */
        $hydrator = $container->get(ObjectMapper::class);

        return new MappedDeserializer($hydrator, require __DIR__ . '/../_deserialise-mapping.php');
    },
    Deserializer::class => DI\get(MappedDeserializer::class),
];
