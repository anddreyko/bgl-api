<?php

declare(strict_types=1);

namespace Bgl\Tests\Unit\Domain\Locations\Entities;

use Bgl\Core\ValueObjects\DateTime;
use Bgl\Core\ValueObjects\Uuid;
use Bgl\Domain\Locations\Location;
use Bgl\Domain\Locations\LocationAlreadyDeletedException;
use Bgl\Tests\Support\UnitTester;
use Codeception\Attribute\Group;

/**
 * @covers \Bgl\Domain\Locations\Location
 */
#[Group('locations', 'location')]
final class LocationCest
{
    public function testCreateReturnsLocationWithCorrectData(UnitTester $i): void
    {
        $id = new Uuid('77777777-7777-4777-8777-777777777777');
        $userId = new Uuid('b2c3d4e5-f6a7-4b8c-9d0e-1f2a3b4c5d6e');
        $name = 'Board Game Cafe';
        $address = '123 Main St';
        $notes = 'Great place';
        $url = 'https://example.com';
        $createdAt = new DateTime('2026-01-15 20:00:00');

        $location = Location::create($id, $userId, $name, $address, $notes, $url, $createdAt);

        $i->assertSame($id, $location->getId());
        $i->assertSame($userId, $location->getUserId());
        $i->assertSame($name, $location->getName());
        $i->assertSame($address, $location->getAddress());
        $i->assertSame($notes, $location->getNotes());
        $i->assertSame($url, $location->getUrl());
        $i->assertSame($createdAt, $location->getCreatedAt());
        $i->assertNull($location->getDeletedAt());
        $i->assertFalse($location->isDeleted());
    }

    public function testCreateWithNullOptionalFields(UnitTester $i): void
    {
        $location = Location::create(
            new Uuid('77777777-7777-4777-8777-777777777777'),
            new Uuid('c3d4e5f6-a7b8-4c9d-8e1f-2a3b4c5d6e7f'),
            'Home',
            null,
            null,
            null,
            new DateTime(),
        );

        $i->assertNull($location->getAddress());
        $i->assertNull($location->getNotes());
        $i->assertNull($location->getUrl());
    }

    public function testUpdateChangesFields(UnitTester $i): void
    {
        $location = Location::create(
            new Uuid('77777777-7777-4777-8777-777777777777'),
            new Uuid('b2c3d4e5-f6a7-4b8c-9d0e-1f2a3b4c5d6e'),
            'Old Name',
            'Old Address',
            'Old Notes',
            'https://old.com',
            new DateTime(),
        );

        $location->update('New Name', 'New Address', 'New Notes', 'https://new.com');

        $i->assertSame('New Name', $location->getName());
        $i->assertSame('New Address', $location->getAddress());
        $i->assertSame('New Notes', $location->getNotes());
        $i->assertSame('https://new.com', $location->getUrl());
    }

    public function testSoftDeleteSetsDeletedAt(UnitTester $i): void
    {
        $location = Location::create(
            new Uuid('77777777-7777-4777-8777-777777777777'),
            new Uuid('b2c3d4e5-f6a7-4b8c-9d0e-1f2a3b4c5d6e'),
            'Cafe',
            null,
            null,
            null,
            new DateTime(),
        );

        $deletedAt = new DateTime('2026-02-20 12:00:00');
        $location->softDelete($deletedAt);

        $i->assertTrue($location->isDeleted());
        $i->assertSame($deletedAt, $location->getDeletedAt());
    }

    public function testSoftDeleteThrowsWhenAlreadyDeleted(UnitTester $i): void
    {
        $location = Location::create(
            new Uuid('77777777-7777-4777-8777-777777777777'),
            new Uuid('b2c3d4e5-f6a7-4b8c-9d0e-1f2a3b4c5d6e'),
            'Cafe',
            null,
            null,
            null,
            new DateTime(),
        );

        $location->softDelete(new DateTime());

        $i->expectThrowable(
            new LocationAlreadyDeletedException(),
            static function () use ($location): void {
                $location->softDelete(new DateTime());
            },
        );
    }
}
