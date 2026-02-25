<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Persistence\Doctrine\Mapping\Plays;

use Bgl\Domain\Plays\Entities\Play;
use Bgl\Domain\Plays\Entities\PlayStatus;
use Bgl\Infrastructure\Persistence\Doctrine\Mapping\EntityMapping;
use Doctrine\ORM\Mapping\ClassMetadata;

final class PlayMapping implements EntityMapping
{
    #[\Override]
    public function getEntityClass(): string
    {
        return Play::class;
    }

    #[\Override]
    public function configure(ClassMetadata $metadata): void
    {
        $metadata->setPrimaryTable(['name' => 'plays_session']);

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

        $metadata->mapField([
            'fieldName' => 'name',
            'type' => 'string',
            'nullable' => true,
        ]);

        $metadata->mapField([
            'fieldName' => 'status',
            'type' => 'string',
            'enumType' => PlayStatus::class,
        ]);

        $metadata->mapField([
            'fieldName' => 'startedAt',
            'type' => 'datetime_immutable',
        ]);

        $metadata->mapField([
            'fieldName' => 'finishedAt',
            'type' => 'datetime_immutable',
            'nullable' => true,
        ]);
    }
}
