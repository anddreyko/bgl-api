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

    public function setUp(): void
    {
        $this->now = new \DateTimeImmutable();
        $this->expires = $this->now->add(new \DateInterval('PT1H'));
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

    public function testValidate(): void
    {
        $token = new Token('00000000-0000-0000-0000-000000000000', $this->expires);
        $this->assertTrue($token->validate('00000000-0000-0000-0000-000000000000'));
    }

    public function testValidateNotEqValue(): void
    {
        $token = new Token('00000000-0000-0000-0000-000000000000', $this->expires);
        $this->assertFalse($token->validate('00000000-0000-0000-0000-000000000001'));
    }

    public function testValidateExpired(): void
    {
        $token = new Token('00000000-0000-0000-0000-000000000000', $this->now);
        $this->assertFalse($token->validate('00000000-0000-0000-0000-000000000000'));
    }

    public function testExpires(): void
    {
        $token = new Token('00000000-0000-0000-0000-000000000000', $this->now);
        $this->assertEquals($this->now, $token->getExpires());
    }

    public function testNotEmpty(): void
    {
        $token = new Token('00000000-0000-0000-0000-000000000000', $this->now);

        $this->assertFalse($token->isEmpty());
    }
}
