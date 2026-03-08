<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Persistence\Doctrine\Mapping\Locations;

use Bgl\Domain\Locations\Location;
use Bgl\Infrastructure\Persistence\Doctrine\Mapping\EntityMapping;
use Doctrine\ORM\Mapping\ClassMetadata;

final class LocationMapping implements EntityMapping
{
    #[\Override]
    public function getEntityClass(): string
    {
        return Location::class;
    }

    #[\Override]
    public function configure(ClassMetadata $metadata): void
    {
        $metadata->setPrimaryTable([
            'name' => 'locations_location',
            'indexes' => [
                'idx_locations_user_id' => ['columns' => ['user_id']],
                'idx_locations_deleted_at' => ['columns' => ['deleted_at']],
            ],
        ]);

        $this->configureIdentity($metadata);
        $this->configureFields($metadata);
    }

    private function configureIdentity(ClassMetadata $metadata): void
    {
        $metadata->mapField([
            'fieldName' => 'id',
            'type' => 'uuid_vo',
            'id' => true,
        ]);
        $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);

        $metadata->mapField([
            'fieldName' => 'userId',
            'type' => 'uuid_vo',
            'columnName' => 'user_id',
        ]);
    }

    private function configureFields(ClassMetadata $metadata): void
    {
        $metadata->mapField([
            'fieldName' => 'name',
            'type' => 'string',
            'length' => 255,
        ]);

        $metadata->mapField([
            'fieldName' => 'address',
            'type' => 'string',
            'length' => 255,
            'nullable' => true,
        ]);

        $metadata->mapField([
            'fieldName' => 'notes',
            'type' => 'text',
            'nullable' => true,
        ]);

        $metadata->mapField([
            'fieldName' => 'url',
            'type' => 'string',
            'length' => 500,
            'nullable' => true,
        ]);

        $metadata->mapField([
            'fieldName' => 'deletedAt',
            'type' => 'datetime_immutable',
            'nullable' => true,
        ]);

        $metadata->mapField([
            'fieldName' => 'createdAt',
            'type' => 'datetime_immutable',
        ]);
    }
}
