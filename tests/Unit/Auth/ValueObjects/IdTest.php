<?php

declare(strict_types=1);

namespace Tests\Unit\Auth\ValueObjects;

use App\Auth\ValueObjects\Id;
use Codeception\Test\Unit;

/**
 * @covers \App\Auth\ValueObjects\Id
 */
class IdTest extends Unit
{
    public function testNilUuid(): void
    {
        $id = new Id('00000000-0000-0000-0000-000000000000');

        $this->assertEquals('00000000-0000-0000-0000-000000000000', $id->getValue());
    }

    public function testToString(): void
    {
        $id = new Id('00000000-0000-0000-0000-000000000000');

        $this->assertEquals('00000000-0000-0000-0000-000000000000', $id);
    }

    public function testSuccessfulCreate(): void
    {
        $id = Id::create();

        $this->assertNotEmpty($id->getValue());
    }

    public function testEmpty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Id('');
    }

    public function testNotUuid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Id('not-uuid');
    }
}
