<?php

declare(strict_types=1);

use Bgl\Infrastructure\Serialization\FractalSerializer;
use Bgl\Tests\Support\Repositories\TestEntity;
use League\Fractal\Manager;
use Psr\Container\ContainerInterface;

return [
    FractalSerializer::class => static function (ContainerInterface $container): FractalSerializer {
        /** @var Manager $manager */
        $manager = $container->get(Manager::class);

        return new FractalSerializer(
            $manager,
            (require __DIR__ . '/../_serialise-mapping.php') + [
                TestEntity::class => static fn(TestEntity $entity) => [
                    'id' => $entity->getId(),
                    'value' => $entity->getValue(),
                    'status' => $entity->getStatus(),
                ],
            ]
        );
    },
];
