<?php

declare(strict_types=1);

namespace Bgl\Tests\Unit\Infrastructure\Persistence\Doctrine\Mapping\Plays;

use Bgl\Domain\Plays\Entities\Session;
use Bgl\Infrastructure\Persistence\Doctrine\Mapping\Plays\SessionMapping;
use Bgl\Tests\Support\UnitTester;
use Codeception\Attribute\Group;
use Doctrine\ORM\Mapping\ClassMetadata;

/**
 * @covers \Bgl\Infrastructure\Persistence\Doctrine\Mapping\Plays\SessionMapping
 */
#[Group('doctrine', 'mapping')]
final class SessionMappingCest
{
    public function testGetEntityClass(UnitTester $i): void
    {
        $mapping = new SessionMapping();

        $i->assertSame(Session::class, $mapping->getEntityClass());
    }

    public function testConfigureSetsTableName(UnitTester $i): void
    {
        $mapping = new SessionMapping();
        $metadata = new ClassMetadata(Session::class);

        $mapping->configure($metadata);

        $i->assertSame('plays_session', $metadata->getTableName());
    }

    public function testConfigureSetsIdField(UnitTester $i): void
    {
        $mapping = new SessionMapping();
        $metadata = new ClassMetadata(Session::class);

        $mapping->configure($metadata);

        $i->assertSame(['id'], $metadata->getIdentifierFieldNames());
        $i->assertSame(ClassMetadata::GENERATOR_TYPE_NONE, $metadata->generatorType);
        $i->assertSame('guid', $metadata->getTypeOfField('id'));
    }

    public function testConfigureSetsUserIdField(UnitTester $i): void
    {
        $mapping = new SessionMapping();
        $metadata = new ClassMetadata(Session::class);

        $mapping->configure($metadata);

        $i->assertSame('string', $metadata->getTypeOfField('userId'));
        $i->assertSame('user_id', $metadata->getColumnName('userId'));
    }

    public function testConfigureSetsNameField(UnitTester $i): void
    {
        $mapping = new SessionMapping();
        $metadata = new ClassMetadata(Session::class);

        $mapping->configure($metadata);

        $i->assertSame('string', $metadata->getTypeOfField('name'));
        $i->assertTrue($metadata->fieldMappings['name']['nullable']);
    }

    public function testConfigureSetsStatusField(UnitTester $i): void
    {
        $mapping = new SessionMapping();
        $metadata = new ClassMetadata(Session::class);

        $mapping->configure($metadata);

        $i->assertSame('string', $metadata->getTypeOfField('status'));
    }

    public function testConfigureSetsStartedAtField(UnitTester $i): void
    {
        $mapping = new SessionMapping();
        $metadata = new ClassMetadata(Session::class);

        $mapping->configure($metadata);

        $i->assertSame('datetime_immutable', $metadata->getTypeOfField('startedAt'));
    }

    public function testConfigureSetsFinishedAtField(UnitTester $i): void
    {
        $mapping = new SessionMapping();
        $metadata = new ClassMetadata(Session::class);

        $mapping->configure($metadata);

        $i->assertSame('datetime_immutable', $metadata->getTypeOfField('finishedAt'));
        $i->assertTrue($metadata->fieldMappings['finishedAt']['nullable']);
    }

    public function testConfigureSetsAllExpectedFields(UnitTester $i): void
    {
        $mapping = new SessionMapping();
        $metadata = new ClassMetadata(Session::class);

        $mapping->configure($metadata);

        $fieldNames = $metadata->getFieldNames();
        sort($fieldNames);

        $i->assertSame(['finishedAt', 'id', 'name', 'startedAt', 'status', 'userId'], $fieldNames);
    }
}
