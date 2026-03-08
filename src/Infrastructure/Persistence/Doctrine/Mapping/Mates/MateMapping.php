<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Persistence\Doctrine\Mapping\Mates;

use Bgl\Domain\Mates\Mate;
use Bgl\Infrastructure\Persistence\Doctrine\Mapping\EntityMapping;
use Doctrine\ORM\Mapping\ClassMetadata;

final class MateMapping implements EntityMapping
{
    #[\Override]
    public function getEntityClass(): string
    {
        return Mate::class;
    }

    #[\Override]
    public function configure(ClassMetadata $metadata): void
    {
        $metadata->setPrimaryTable([
            'name' => 'mates_mate',
            'indexes' => [
                'idx_mates_mate_deleted_at' => ['columns' => ['deleted_at']],
                'idx_mates_user_name' => ['columns' => ['user_id', 'name']],
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
            'nullable' => true,
        ]);
    }

    private function configureFields(ClassMetadata $metadata): void
    {
        $metadata->mapField([
            'fieldName' => 'name',
            'type' => 'string',
            'length' => 100,
        ]);

        $metadata->mapField([
            'fieldName' => 'notes',
            'type' => 'text',
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
