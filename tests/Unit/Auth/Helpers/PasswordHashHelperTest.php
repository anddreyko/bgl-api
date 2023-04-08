<?php

declare(strict_types=1);

namespace Tests\Unit\Auth\Helpers;

use App\Auth\Helpers\PasswordHashHelper;
use Codeception\Test\Unit;

/**
 * @covers \App\Auth\Helpers\PasswordHashHelper
 */
class PasswordHashHelperTest extends Unit
{
    private PasswordHashHelper $hasher;

    protected function _setUp()
    {
        $this->hasher = new PasswordHashHelper(16);

        parent::_setUp();
    }

    public function testSuccess(): void
    {
        $hash = $this->hasher->hash('password');
        $this->assertNotEquals('password', $hash->getValue());
    }

    public function testNotEmpty(): void
    {
        $hash = $this->hasher->hash('password');
        $this->assertNotEmpty($hash->getValue());
    }

    public function testEmpty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->hasher->hash('');
    }

    public function testSpace(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->hasher->hash('   ');
    }

    public function testValidate(): void
    {
        $hash = $this->hasher->hash('password');
        $this->assertTrue($this->hasher->validate('password', $hash));
    }

    public function testValidateNot(): void
    {
        $hash = $this->hasher->hash('password');
        $this->assertFalse($this->hasher->validate('wrong-password', $hash));
    }
}
