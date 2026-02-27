<?php

declare(strict_types=1);

namespace Bgl\Tests\Unit\Domain\Mates\Entities;

use Bgl\Core\ValueObjects\Uuid;
use Bgl\Domain\Mates\Entities\Mate;
use Bgl\Tests\Support\UnitTester;
use Codeception\Attribute\Group;

/**
 * @covers \Bgl\Domain\Mates\Entities\Mate
 */
#[Group('mates', 'mate')]
final class MateCest
{
    public function testCreateReturnsMateWithCorrectData(UnitTester $i): void
    {
        $id = new Uuid('mate-id');
        $userId = new Uuid('user-123');
        $name = 'Ivan';
        $notes = 'Likes Carcassonne';
        $createdAt = new \DateTimeImmutable('2026-01-15 20:00:00');

        $mate = Mate::create($id, $userId, $name, $notes, $createdAt);

        $i->assertSame($id, $mate->getId());
        $i->assertSame($userId, $mate->getUserId());
        $i->assertSame($name, $mate->getName());
        $i->assertSame($notes, $mate->getNotes());
        $i->assertSame($createdAt, $mate->getCreatedAt());
        $i->assertNull($mate->getDeletedAt());
        $i->assertFalse($mate->isDeleted());
    }

    public function testCreateWithNullNotes(UnitTester $i): void
    {
        $mate = Mate::create(
            new Uuid('mate-id'),
            new Uuid('user-456'),
            'Anna',
            null,
            new \DateTimeImmutable(),
        );

        $i->assertNull($mate->getNotes());
    }

    public function testUpdateChangesNameAndNotes(UnitTester $i): void
    {
        $mate = Mate::create(
            new Uuid('mate-id'),
            new Uuid('user-123'),
            'Ivan',
            'Old notes',
            new \DateTimeImmutable(),
        );

        $mate->update('Ivan Petrov', 'New notes');

        $i->assertSame('Ivan Petrov', $mate->getName());
        $i->assertSame('New notes', $mate->getNotes());
    }

    public function testSoftDeleteSetsDeletedAt(UnitTester $i): void
    {
        $mate = Mate::create(
            new Uuid('mate-id'),
            new Uuid('user-123'),
            'Ivan',
            null,
            new \DateTimeImmutable(),
        );

        $deletedAt = new \DateTimeImmutable('2026-02-20 12:00:00');
        $mate->softDelete($deletedAt);

        $i->assertTrue($mate->isDeleted());
        $i->assertSame($deletedAt, $mate->getDeletedAt());
    }

    public function testSoftDeleteThrowsWhenAlreadyDeleted(UnitTester $i): void
    {
        $mate = Mate::create(
            new Uuid('mate-id'),
            new Uuid('user-123'),
            'Ivan',
            null,
            new \DateTimeImmutable(),
        );

        $mate->softDelete(new \DateTimeImmutable());

        $i->expectThrowable(
            new \DomainException('Mate is already deleted'),
            static function () use ($mate): void {
                $mate->softDelete(new \DateTimeImmutable());
            },
        );
    }
}
