<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Persistence\Doctrine\Mapping\Plays;

use Bgl\Domain\Plays\Entities\Play;
use Bgl\Domain\Plays\Entities\Player;
use Bgl\Infrastructure\Persistence\Doctrine\Mapping\EntityMapping;
use Doctrine\ORM\Mapping\ClassMetadata;

final class PlayerMapping implements EntityMapping
{
    #[\Override]
    public function getEntityClass(): string
    {
        return Player::class;
    }

    #[\Override]
    public function configure(ClassMetadata $metadata): void
    {
        $metadata->setPrimaryTable(['name' => 'plays_player']);

        $this->configureFields($metadata);
        $this->configureAssociations($metadata);
    }

    private function configureFields(ClassMetadata $metadata): void
    {
        $metadata->mapField([
            'fieldName' => 'id',
            'type' => 'uuid_vo',
            'id' => true,
        ]);
        $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);

        $metadata->mapField([
            'fieldName' => 'mateId',
            'type' => 'uuid_vo',
            'columnName' => 'mate_id',
        ]);

        $metadata->mapField([
            'fieldName' => 'score',
            'type' => 'integer',
            'nullable' => true,
        ]);

        $metadata->mapField([
            'fieldName' => 'isWinner',
            'type' => 'boolean',
            'columnName' => 'is_winner',
            'options' => ['default' => false],
        ]);

        $metadata->mapField([
            'fieldName' => 'color',
            'type' => 'string',
            'nullable' => true,
            'length' => 50,
        ]);
    }

    private function configureAssociations(ClassMetadata $metadata): void
    {
        $metadata->mapManyToOne([
            'fieldName' => 'play',
            'targetEntity' => Play::class,
            'inversedBy' => 'players',
            'joinColumns' => [[
                'name' => 'play_id',
                'referencedColumnName' => 'id',
            ]],
        ]);
    }
}
