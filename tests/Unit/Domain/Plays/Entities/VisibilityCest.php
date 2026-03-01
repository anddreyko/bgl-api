<?php

declare(strict_types=1);

namespace Bgl\Tests\Unit\Domain\Plays\Entities;

use Bgl\Domain\Plays\Entities\Visibility;
use Bgl\Tests\Support\UnitTester;
use Codeception\Attribute\Group;

/**
 * @covers \Bgl\Domain\Plays\Entities\Visibility
 */
#[Group('plays', 'visibility')]
final class VisibilityCest
{
    public function testEnumValues(UnitTester $i): void
    {
        $i->assertSame('private', Visibility::Private->value);
        $i->assertSame('link', Visibility::Link->value);
        $i->assertSame('participants', Visibility::Participants->value);
        $i->assertSame('authenticated', Visibility::Authenticated->value);
        $i->assertSame('public', Visibility::Public->value);
    }

    public function testEnumFromString(UnitTester $i): void
    {
        $i->assertSame(Visibility::Private, Visibility::from('private'));
        $i->assertSame(Visibility::Public, Visibility::from('public'));
    }

    public function testEnumCasesCount(UnitTester $i): void
    {
        $i->assertCount(5, Visibility::cases());
    }
}
