<?php

declare(strict_types=1);

namespace Bgl\Tests\Unit\Core\Validation;

use Bgl\Core\Validation\ValidationResult;
use Bgl\Tests\Support\UnitTester;
use Codeception\Attribute\Group;

/**
 * @covers \Bgl\Core\Validation\ValidationResult
 */
#[Group('core', 'validation')]
final class ValidationResultCest
{
    public function testEmptyResultHasNoErrors(UnitTester $i): void
    {
        $result = new ValidationResult();

        $i->assertFalse($result->hasErrors());
        $i->assertSame([], $result->getErrors());
    }

    public function testAddErrorReturnsNewInstance(UnitTester $i): void
    {
        $result = new ValidationResult();
        $withError = $result->addError('email', 'Invalid email');

        $i->assertFalse($result->hasErrors());
        $i->assertTrue($withError->hasErrors());
    }

    public function testAddErrorAccumulatesErrors(UnitTester $i): void
    {
        $result = new ValidationResult();
        $result = $result->addError('email', 'Required');
        $result = $result->addError('email', 'Invalid format');
        $result = $result->addError('name', 'Too short');

        $i->assertTrue($result->hasErrors());
        $i->assertSame(
            [
                'email' => ['Required', 'Invalid format'],
                'name' => ['Too short'],
            ],
            $result->getErrors(),
        );
    }

    public function testConstructorWithErrors(UnitTester $i): void
    {
        $errors = ['field' => ['Error message']];
        $result = new ValidationResult($errors);

        $i->assertTrue($result->hasErrors());
        $i->assertSame($errors, $result->getErrors());
    }
}
