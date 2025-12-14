<?php

declare(strict_types=1);

namespace Bgl\Tests\Unit\Infrastructure\Persistence\Doctrine\Mapping\Auth;

use Bgl\Domain\Auth\Entities\User;
use Bgl\Infrastructure\Persistence\Doctrine\Mapping\Auth\UserMapping;
use Bgl\Tests\Support\UnitTester;
use Codeception\Attribute\Group;
use Doctrine\ORM\Mapping\ClassMetadata;

/**
 * @covers \Bgl\Infrastructure\Persistence\Doctrine\Mapping\Auth\UserMapping
 */
#[Group('doctrine', 'mapping')]
class UserMappingCest
{
    public function testGetEntityClass(UnitTester $i): void
    {
        $mapping = new UserMapping();

        $i->assertSame(User::class, $mapping->getEntityClass());
    }

    public function testConfigureSetsTableName(UnitTester $i): void
    {
        $mapping = new UserMapping();
        $metadata = new ClassMetadata(User::class);

        $mapping->configure($metadata);

        $i->assertSame('auth_user', $metadata->getTableName());
    }

    public function testConfigureSetsIdField(UnitTester $i): void
    {
        $mapping = new UserMapping();
        $metadata = new ClassMetadata(User::class);

        $mapping->configure($metadata);

        $i->assertSame(['id'], $metadata->getIdentifierFieldNames());
        $i->assertSame(ClassMetadata::GENERATOR_TYPE_NONE, $metadata->generatorType);
        $i->assertSame('guid', $metadata->getTypeOfField('id'));
    }

    public function testConfigureSetsEmailField(UnitTester $i): void
    {
        $mapping = new UserMapping();
        $metadata = new ClassMetadata(User::class);

        $mapping->configure($metadata);

        $i->assertSame('string', $metadata->getTypeOfField('email'));
    }

    public function testConfigureSetsCreatedAtField(UnitTester $i): void
    {
        $mapping = new UserMapping();
        $metadata = new ClassMetadata(User::class);

        $mapping->configure($metadata);

        $i->assertSame('date_immutable', $metadata->getTypeOfField('createdAt'));
    }

    public function testConfigureSetsStatusField(UnitTester $i): void
    {
        $mapping = new UserMapping();
        $metadata = new ClassMetadata(User::class);

        $mapping->configure($metadata);

        $i->assertSame('string', $metadata->getTypeOfField('status'));
    }

    public function testConfigureSetsAllExpectedFields(UnitTester $i): void
    {
        $mapping = new UserMapping();
        $metadata = new ClassMetadata(User::class);

        $mapping->configure($metadata);

        $fieldNames = $metadata->getFieldNames();
        sort($fieldNames);

        $i->assertSame(['createdAt', 'email', 'id', 'status'], $fieldNames);
    }
}
