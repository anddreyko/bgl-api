<?php

declare(strict_types=1);

namespace Bgl\Tests\Unit\Core\Validation\Attributes;

use Bgl\Core\Validation\Attributes\NotBlank;
use Bgl\Tests\Support\UnitTester;
use Codeception\Attribute\Group;

/**
 * @covers \Bgl\Core\Validation\Attributes\NotBlank
 */
#[Group('core', 'validation', 'attributes')]
final class NotBlankCest
{
    public function testDefaultMessage(UnitTester $i): void
    {
        $attr = new NotBlank();

        $i->assertSame('This value should not be blank.', $attr->message);
    }

    public function testCustomMessage(UnitTester $i): void
    {
        $attr = new NotBlank(message: 'Field is required.');

        $i->assertSame('Field is required.', $attr->message);
    }
}
