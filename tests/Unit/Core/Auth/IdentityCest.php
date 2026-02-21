<?php

declare(strict_types=1);

namespace Bgl\Tests\Unit\Core\Auth;

use Bgl\Core\Auth\Identity;
use Bgl\Core\ValueObjects\Uuid;
use Bgl\Tests\Support\UnitTester;
use Codeception\Attribute\Group;

/**
 * @covers \Bgl\Core\Auth\Identity
 */
#[Group('core', 'auth', 'identity')]
final class IdentityCest
{
    public function testGetId(UnitTester $i): void
    {
        $uuid = new Uuid('550e8400-e29b-41d4-a716-446655440000');
        $identity = new Identity($uuid);

        $i->assertSame($uuid, $identity->getId());
    }

    public function testEqualsWithSameId(UnitTester $i): void
    {
        $uuid1 = new Uuid('550e8400-e29b-41d4-a716-446655440000');
        $uuid2 = new Uuid('550e8400-e29b-41d4-a716-446655440000');

        $identity1 = new Identity($uuid1);
        $identity2 = new Identity($uuid2);

        $i->assertTrue($identity1->equals($identity2));
    }

    public function testEqualsWithDifferentId(UnitTester $i): void
    {
        $uuid1 = new Uuid('550e8400-e29b-41d4-a716-446655440000');
        $uuid2 = new Uuid('550e8400-e29b-41d4-a716-446655440001');

        $identity1 = new Identity($uuid1);
        $identity2 = new Identity($uuid2);

        $i->assertFalse($identity1->equals($identity2));
    }

    public function testEqualsWithNullUuids(UnitTester $i): void
    {
        $uuid1 = new Uuid(null);
        $uuid2 = new Uuid(null);

        $identity1 = new Identity($uuid1);
        $identity2 = new Identity($uuid2);

        $i->assertTrue($identity1->equals($identity2));
    }

    public function testNotEqualsWhenOneIsNull(UnitTester $i): void
    {
        $uuid1 = new Uuid('550e8400-e29b-41d4-a716-446655440000');
        $uuid2 = new Uuid(null);

        $identity1 = new Identity($uuid1);
        $identity2 = new Identity($uuid2);

        $i->assertFalse($identity1->equals($identity2));
    }
}
