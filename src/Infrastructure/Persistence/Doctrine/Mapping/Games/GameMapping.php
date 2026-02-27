<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Persistence\Doctrine\Mapping\Games;

use Bgl\Domain\Games\Entities\Game;
use Bgl\Infrastructure\Persistence\Doctrine\Mapping\EntityMapping;
use Doctrine\ORM\Mapping\ClassMetadata;

final class GameMapping implements EntityMapping
{
    #[\Override]
    public function getEntityClass(): string
    {
        return Game::class;
    }

    #[\Override]
    public function configure(ClassMetadata $metadata): void
    {
        $metadata->setPrimaryTable(['name' => 'games_game']);

        $metadata->mapField([
            'fieldName' => 'id',
            'type' => 'uuid_vo',
            'id' => true,
        ]);
        $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);

        $metadata->mapField([
            'fieldName' => 'bggId',
            'type' => 'integer',
            'columnName' => 'bgg_id',
            'unique' => true,
        ]);

        $metadata->mapField([
            'fieldName' => 'name',
            'type' => 'string',
            'length' => 255,
        ]);

        $metadata->mapField([
            'fieldName' => 'yearPublished',
            'type' => 'integer',
            'nullable' => true,
        ]);

        $metadata->mapField([
            'fieldName' => 'createdAt',
            'type' => 'datetime_immutable',
        ]);

        $metadata->mapField([
            'fieldName' => 'updatedAt',
            'type' => 'datetime_immutable',
        ]);
    }
}
