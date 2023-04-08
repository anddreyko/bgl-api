<?php

declare(strict_types=1);

namespace Tests\Unit\Auth\ValueObjects;

use App\Auth\ValueObjects\Token;
use Codeception\Test\Unit;

/**
 * @covers \App\Auth\ValueObjects\Token
 */
class TokenTest extends Unit
{
    private \DateTimeImmutable $now;
    private \DateTimeImmutable $expires;

    protected function _setUp(): void
    {
        $this->now = new \DateTimeImmutable();
        $this->expires = $this->now->add(new \DateInterval('PT1H'));

        parent::_setUp();
    }

    public function testNilUuid(): void
    {
        $id = new Token('00000000-0000-0000-0000-000000000000', $this->expires);

        $this->assertEquals('00000000-0000-0000-0000-000000000000', $id->getValue());
    }

    public function testSuccessfulCreate(): void
    {
        $id = Token::create($this->expires);

        $this->assertNotEmpty($id->getValue());
    }

    public function testEmpty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Token('', $this->expires);
    }

    public function testNotUuid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Token('not-uuid', $this->expires);
    }

    public function testCheck(): void
    {
        $token = new Token('00000000-0000-0000-0000-000000000000', $this->expires);
        $this->assertTrue($token->check('00000000-0000-0000-0000-000000000000'));
    }

    public function testCheckNotEqValue(): void
    {
        $token = new Token('00000000-0000-0000-0000-000000000000', $this->expires);
        $this->assertFalse($token->check('00000000-0000-0000-0000-000000000001'));
    }

    public function testCheckExpired(): void
    {
        $token = new Token('00000000-0000-0000-0000-000000000000', $this->now);
        $this->assertFalse($token->check('00000000-0000-0000-0000-000000000000'));
    }

    public function testExpires(): void
    {
        $token = new Token('00000000-0000-0000-0000-000000000000', $this->now);
        $this->assertEquals($this->now, $token->getExpires());
    }
}
