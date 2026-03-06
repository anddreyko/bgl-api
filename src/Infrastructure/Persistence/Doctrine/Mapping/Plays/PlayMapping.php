<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Persistence\Doctrine\Mapping\Plays;

use Bgl\Domain\Plays\Play;
use Bgl\Domain\Plays\Player\Player;
use Bgl\Domain\Plays\PlayStatus;
use Bgl\Domain\Plays\Visibility;
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
        $metadata->setPrimaryTable([
            'name' => 'plays_session',
            'indexes' => [
                'idx_plays_session_user_id' => ['columns' => ['user_id']],
                'idx_plays_session_started_at' => ['columns' => ['started_at']],
                'idx_plays_session_game_id' => ['columns' => ['game_id']],
            ],
        ]);

        $this->configureIdentity($metadata);
        $this->configureFields($metadata);
        $this->configureAssociations($metadata);
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

        $metadata->mapField([
            'fieldName' => 'gameId',
            'type' => 'uuid_vo',
            'columnName' => 'game_id',
            'nullable' => true,
        ]);

        $metadata->mapField([
            'fieldName' => 'visibility',
            'type' => 'string',
            'enumType' => Visibility::class,
            'options' => ['default' => 'private'],
        ]);
    }

    private function configureAssociations(ClassMetadata $metadata): void
    {
        $metadata->mapOneToMany([
            'fieldName' => 'players',
            'targetEntity' => Player::class,
            'mappedBy' => 'play',
            'cascade' => ['persist'],
            'orphanRemoval' => true,
        ]);
    }
}
