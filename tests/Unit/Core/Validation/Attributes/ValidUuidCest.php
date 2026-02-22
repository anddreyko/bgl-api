<?php

declare(strict_types=1);

namespace Bgl\Tests\Unit\Core\Validation\Attributes;

use Bgl\Core\Validation\Attributes\ValidUuid;
use Bgl\Tests\Support\UnitTester;
use Codeception\Attribute\Group;

/**
 * @covers \Bgl\Core\Validation\Attributes\ValidUuid
 */
#[Group('core', 'validation', 'attributes')]
final class ValidUuidCest
{
    public function testDefaultMessage(UnitTester $i): void
    {
        $attr = new ValidUuid();

        $i->assertSame('This value is not a valid UUID.', $attr->message);
    }

    public function testCustomMessage(UnitTester $i): void
    {
        $attr = new ValidUuid(message: 'Invalid identifier.');

        $i->assertSame('Invalid identifier.', $attr->message);
    }
}
