<?php

declare(strict_types=1);

namespace Bgl\Tests\Unit\Infrastructure\Persistence\Doctrine\Mapping\Plays;

use Bgl\Domain\Plays\Play;
use Bgl\Domain\Plays\Player\Player;
use Bgl\Infrastructure\Persistence\Doctrine\Mapping\Plays\PlayerMapping;
use Bgl\Tests\Support\UnitTester;
use Codeception\Attribute\Group;
use Doctrine\ORM\Mapping\ClassMetadata;

/**
 * @covers \Bgl\Infrastructure\Persistence\Doctrine\Mapping\Plays\PlayerMapping
 */
#[Group('doctrine', 'mapping')]
final class PlayerMappingCest
{
    public function testGetEntityClass(UnitTester $i): void
    {
        $mapping = new PlayerMapping();

        $i->assertSame(Player::class, $mapping->getEntityClass());
    }

    public function testConfigureSetsTableName(UnitTester $i): void
    {
        $mapping = new PlayerMapping();
        $metadata = new ClassMetadata(Player::class);

        $mapping->configure($metadata);

        $i->assertSame('plays_player', $metadata->getTableName());
    }

    public function testConfigureSetsIdField(UnitTester $i): void
    {
        $mapping = new PlayerMapping();
        $metadata = new ClassMetadata(Player::class);

        $mapping->configure($metadata);

        $i->assertSame(['id'], $metadata->getIdentifierFieldNames());
        $i->assertSame(ClassMetadata::GENERATOR_TYPE_NONE, $metadata->generatorType);
        $i->assertSame('uuid_vo', $metadata->getTypeOfField('id'));
    }

    public function testConfigureSetsPlayAssociation(UnitTester $i): void
    {
        $mapping = new PlayerMapping();
        $metadata = new ClassMetadata(Player::class);

        $mapping->configure($metadata);

        $i->assertTrue($metadata->hasAssociation('play'));
        $association = $metadata->getAssociationMapping('play');
        $i->assertSame(Play::class, $association['targetEntity']);
        $i->assertSame('players', $association['inversedBy']);
    }

    public function testConfigureSetsMateIdField(UnitTester $i): void
    {
        $mapping = new PlayerMapping();
        $metadata = new ClassMetadata(Player::class);

        $mapping->configure($metadata);

        $i->assertSame('uuid_vo', $metadata->getTypeOfField('mateId'));
        $i->assertSame('mate_id', $metadata->getColumnName('mateId'));
    }

    public function testConfigureSetsScoreField(UnitTester $i): void
    {
        $mapping = new PlayerMapping();
        $metadata = new ClassMetadata(Player::class);

        $mapping->configure($metadata);

        $i->assertSame('integer', $metadata->getTypeOfField('score'));
        $i->assertTrue($metadata->fieldMappings['score']['nullable']);
    }

    public function testConfigureSetsIsWinnerField(UnitTester $i): void
    {
        $mapping = new PlayerMapping();
        $metadata = new ClassMetadata(Player::class);

        $mapping->configure($metadata);

        $i->assertSame('boolean', $metadata->getTypeOfField('isWinner'));
        $i->assertSame('is_winner', $metadata->getColumnName('isWinner'));
        $i->assertSame(false, $metadata->fieldMappings['isWinner']['options']['default']);
    }

    public function testConfigureSetsColorField(UnitTester $i): void
    {
        $mapping = new PlayerMapping();
        $metadata = new ClassMetadata(Player::class);

        $mapping->configure($metadata);

        $i->assertSame('string', $metadata->getTypeOfField('color'));
        $i->assertTrue($metadata->fieldMappings['color']['nullable']);
        $i->assertSame(50, $metadata->fieldMappings['color']['length']);
    }

    public function testConfigureSetsAllExpectedFields(UnitTester $i): void
    {
        $mapping = new PlayerMapping();
        $metadata = new ClassMetadata(Player::class);

        $mapping->configure($metadata);

        $fieldNames = $metadata->getFieldNames();
        sort($fieldNames);

        $i->assertSame(['color', 'id', 'isWinner', 'mateId', 'number', 'score', 'teamTag'], $fieldNames);
    }
}
