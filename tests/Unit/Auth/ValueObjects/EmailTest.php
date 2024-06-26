<?php

declare(strict_types=1);

namespace Tests\Unit\Auth\ValueObjects;

use App\Core\ValueObjects\Email;
use Codeception\Test\Unit;

/**
 * @covers \App\Core\ValueObjects\Email
 */
class EmailTest extends Unit
{
    public function testSuccessful(): void
    {
        $email = new Email('test@te.st.test');

        $this->assertEquals('test@te.st.test', $email->getValue());
    }

    public function testNotEmpty(): void
    {
        $email = new Email('test@te.st.test');

        $this->assertNotEmpty($email->getValue());
    }

    public function testEmpty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Email('');
    }

    public function testNotEmail(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Email('not-uuid');
    }

    public function testSpace(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Email(' ');
    }

    public function testAddSpace(): void
    {
        $email = new Email(' test@t.est');
        $this->assertEquals('test@t.est', $email->getValue());
    }
}
