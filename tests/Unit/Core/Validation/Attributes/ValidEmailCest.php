<?php

declare(strict_types=1);

namespace Bgl\Tests\Unit\Core\Validation\Attributes;

use Bgl\Core\Validation\Attributes\ValidEmail;
use Bgl\Tests\Support\UnitTester;
use Codeception\Attribute\Group;

/**
 * @covers \Bgl\Core\Validation\Attributes\ValidEmail
 */
#[Group('core', 'validation', 'attributes')]
final class ValidEmailCest
{
    public function testDefaultMessage(UnitTester $i): void
    {
        $attr = new ValidEmail();

        $i->assertSame('This value is not a valid email address.', $attr->message);
    }

    public function testCustomMessage(UnitTester $i): void
    {
        $attr = new ValidEmail(message: 'Bad email.');

        $i->assertSame('Bad email.', $attr->message);
    }
}
