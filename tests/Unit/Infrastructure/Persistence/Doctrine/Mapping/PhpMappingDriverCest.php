<?php

declare(strict_types=1);

namespace Bgl\Tests\Unit\Infrastructure\Persistence\Doctrine\Mapping;

use Bgl\Domain\Auth\Entities\User;
use Bgl\Infrastructure\Persistence\Doctrine\Mapping\Auth\UserMapping;
use Bgl\Infrastructure\Persistence\Doctrine\Mapping\PhpMappingDriver;
use Bgl\Tests\Support\UnitTester;
use Codeception\Attribute\Group;
use Doctrine\ORM\Mapping\ClassMetadata;

/**
 * @covers \Bgl\Infrastructure\Persistence\Doctrine\Mapping\PhpMappingDriver
 */
#[Group('doctrine', 'mapping')]
class PhpMappingDriverCest
{
    public function testGetAllClassNames(UnitTester $i): void
    {
        $driver = new PhpMappingDriver([new UserMapping()]);

        $classNames = $driver->getAllClassNames();

        $i->assertSame([User::class], $classNames);
    }

    public function testGetAllClassNamesEmpty(UnitTester $i): void
    {
        $driver = new PhpMappingDriver([]);

        $i->assertSame([], $driver->getAllClassNames());
    }

    public function testIsTransientReturnsFalseForMappedClass(UnitTester $i): void
    {
        $driver = new PhpMappingDriver([new UserMapping()]);

        $i->assertFalse($driver->isTransient(User::class));
    }

    public function testIsTransientReturnsTrueForUnmappedClass(UnitTester $i): void
    {
        $driver = new PhpMappingDriver([]);

        $i->assertTrue($driver->isTransient(User::class));
    }

    public function testLoadMetadataForClassThrowsForUnmappedClass(UnitTester $i): void
    {
        $driver = new PhpMappingDriver([]);

        $i->expectThrowable(\InvalidArgumentException::class, static function () use ($driver): void {
            $metadata = new ClassMetadata(User::class);
            $driver->loadMetadataForClass(User::class, $metadata);
        });
    }

    public function testLoadMetadataForClassDelegatesToMapping(UnitTester $i): void
    {
        $driver = new PhpMappingDriver([new UserMapping()]);
        $metadata = new ClassMetadata(User::class);

        $driver->loadMetadataForClass(User::class, $metadata);

        $i->assertSame('auth_user', $metadata->getTableName());
        $i->assertSame(ClassMetadata::GENERATOR_TYPE_NONE, $metadata->generatorType);
        $i->assertSame(['id'], $metadata->getIdentifierFieldNames());
    }
}
