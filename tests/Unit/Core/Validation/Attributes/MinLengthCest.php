<?php

declare(strict_types=1);

namespace Bgl\Tests\Unit\Core\Validation\Attributes;

use Bgl\Core\Validation\Attributes\MinLength;
use Bgl\Tests\Support\UnitTester;
use Codeception\Attribute\Group;

/**
 * @covers \Bgl\Core\Validation\Attributes\MinLength
 */
#[Group('core', 'validation', 'attributes')]
final class MinLengthCest
{
    public function testDefaultMessage(UnitTester $i): void
    {
        $attr = new MinLength(min: 8);

        $i->assertSame(8, $attr->min);
        $i->assertSame('This value is too short. It should have %d characters or more.', $attr->message);
    }

    public function testCustomMessage(UnitTester $i): void
    {
        $attr = new MinLength(min: 3, message: 'At least %d chars.');

        $i->assertSame(3, $attr->min);
        $i->assertSame('At least %d chars.', $attr->message);
    }
}
