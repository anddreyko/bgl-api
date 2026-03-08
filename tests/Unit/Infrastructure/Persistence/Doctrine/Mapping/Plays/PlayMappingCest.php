<?php

declare(strict_types=1);

namespace Bgl\Tests\Unit\Infrastructure\Persistence\Doctrine\Mapping\Plays;

use Bgl\Domain\Plays\Play;
use Bgl\Infrastructure\Persistence\Doctrine\Mapping\Plays\PlayMapping;
use Bgl\Tests\Support\UnitTester;
use Codeception\Attribute\Group;
use Doctrine\ORM\Mapping\ClassMetadata;

/**
 * @covers \Bgl\Infrastructure\Persistence\Doctrine\Mapping\Plays\PlayMapping
 */
#[Group('doctrine', 'mapping')]
final class PlayMappingCest
{
    public function testGetEntityClass(UnitTester $i): void
    {
        $mapping = new PlayMapping();

        $i->assertSame(Play::class, $mapping->getEntityClass());
    }

    public function testConfigureSetsTableName(UnitTester $i): void
    {
        $mapping = new PlayMapping();
        $metadata = new ClassMetadata(Play::class);

        $mapping->configure($metadata);

        $i->assertSame('plays_session', $metadata->getTableName());
    }

    public function testConfigureSetsIdField(UnitTester $i): void
    {
        $mapping = new PlayMapping();
        $metadata = new ClassMetadata(Play::class);

        $mapping->configure($metadata);

        $i->assertSame(['id'], $metadata->getIdentifierFieldNames());
        $i->assertSame(ClassMetadata::GENERATOR_TYPE_NONE, $metadata->generatorType);
        $i->assertSame('uuid_vo', $metadata->getTypeOfField('id'));
    }

    public function testConfigureSetsUserIdField(UnitTester $i): void
    {
        $mapping = new PlayMapping();
        $metadata = new ClassMetadata(Play::class);

        $mapping->configure($metadata);

        $i->assertSame('uuid_vo', $metadata->getTypeOfField('userId'));
        $i->assertSame('user_id', $metadata->getColumnName('userId'));
    }

    public function testConfigureSetsNameField(UnitTester $i): void
    {
        $mapping = new PlayMapping();
        $metadata = new ClassMetadata(Play::class);

        $mapping->configure($metadata);

        $i->assertSame('string', $metadata->getTypeOfField('name'));
        $i->assertTrue($metadata->fieldMappings['name']['nullable']);
    }

    public function testConfigureSetsStatusField(UnitTester $i): void
    {
        $mapping = new PlayMapping();
        $metadata = new ClassMetadata(Play::class);

        $mapping->configure($metadata);

        $i->assertSame('string', $metadata->getTypeOfField('status'));
    }

    public function testConfigureSetsStartedAtField(UnitTester $i): void
    {
        $mapping = new PlayMapping();
        $metadata = new ClassMetadata(Play::class);

        $mapping->configure($metadata);

        $i->assertSame('datetime_immutable', $metadata->getTypeOfField('startedAt'));
    }

    public function testConfigureSetsFinishedAtField(UnitTester $i): void
    {
        $mapping = new PlayMapping();
        $metadata = new ClassMetadata(Play::class);

        $mapping->configure($metadata);

        $i->assertSame('datetime_immutable', $metadata->getTypeOfField('finishedAt'));
        $i->assertTrue($metadata->fieldMappings['finishedAt']['nullable']);
    }

    public function testConfigureSetsAllExpectedFields(UnitTester $i): void
    {
        $mapping = new PlayMapping();
        $metadata = new ClassMetadata(Play::class);

        $mapping->configure($metadata);

        $fieldNames = $metadata->getFieldNames();
        sort($fieldNames);

        $i->assertSame(['finishedAt', 'gameId', 'id', 'locationId', 'name', 'notes', 'startedAt', 'status', 'userId', 'visibility'], $fieldNames);
    }

    public function testConfigureSetsGameIdField(UnitTester $i): void
    {
        $mapping = new PlayMapping();
        $metadata = new ClassMetadata(Play::class);

        $mapping->configure($metadata);

        $i->assertSame('uuid_vo', $metadata->getTypeOfField('gameId'));
        $i->assertSame('game_id', $metadata->getColumnName('gameId'));
        $i->assertTrue($metadata->fieldMappings['gameId']['nullable']);
    }

    public function testConfigureSetsVisibilityField(UnitTester $i): void
    {
        $mapping = new PlayMapping();
        $metadata = new ClassMetadata(Play::class);

        $mapping->configure($metadata);

        $i->assertSame('string', $metadata->getTypeOfField('visibility'));
        $i->assertSame('private', $metadata->fieldMappings['visibility']['options']['default']);
    }
}
