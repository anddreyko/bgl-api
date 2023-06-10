<?php

declare(strict_types=1);

namespace Tests\Unit\Auth\ValueObjects;

use App\Auth\ValueObjects\WebToken;
use Codeception\Test\Unit;

/**
 * @covers \App\Auth\ValueObjects\WebToken
 */
class WebTokenTest extends Unit
{
    public function testNilUuid(): void
    {
        $token = new WebToken('00000000-0000-0000-0000-000000000000');

        $this->assertEquals('00000000-0000-0000-0000-000000000000', $token->getValue());
    }

    public function testEmpty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new WebToken('');
    }

    public function testEmptyWithSpace(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new WebToken('   ');
    }
}
