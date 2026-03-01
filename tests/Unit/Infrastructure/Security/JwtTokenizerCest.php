<?php

declare(strict_types=1);

namespace Bgl\Tests\Unit\Infrastructure\Security;

use Bgl\Core\Clock;
use Bgl\Core\Security\TokenPayload;
use Bgl\Infrastructure\Security\JwtTokenizer;
use Bgl\Tests\Support\UnitTester;
use Codeception\Attribute\Group;

/**
 * @covers \Bgl\Infrastructure\Security\JwtTokenizer
 */
#[Group('core', 'security', 'tokenizer')]
final class JwtTokenizerCest
{
    private JwtTokenizer $generator;
    private Clock $clock;

    public function _before(): void
    {
        $this->clock = new Clock(stub: new \DateTimeImmutable('2024-06-01 12:00:00', new \DateTimeZone('UTC')));
        $this->generator = new JwtTokenizer(
            secret: 'test-secret-key-that-is-long-enough-for-hmac',
            clock: $this->clock,
        );
    }

    public function testGenerateReturnsNonEmptyString(UnitTester $i): void
    {
        $token = $this->generator->generate(TokenPayload::fromArray(['user_id' => '123']), 3600);

        $i->assertNotEmpty($token);
        $i->assertIsString($token);
    }

    public function testGenerateAndVerifyRoundtripReturnsOriginalPayload(UnitTester $i): void
    {
        $payload = TokenPayload::fromArray(['user_id' => '123', 'role' => 'admin']);
        $token = $this->generator->generate($payload, 3600);
        $result = $this->generator->verify($token);

        $i->assertTrue($result->has('user_id'));
        $i->assertTrue($result->has('role'));
        $i->assertEquals('123', $result->getString('user_id'));
        $i->assertEquals('admin', $result->getString('role'));
    }

    public function testExpiredTokenThrowsException(UnitTester $i): void
    {
        $pastClock = new Clock(stub: new \DateTimeImmutable('2024-01-01 00:00:00', new \DateTimeZone('UTC')));
        $pastGenerator = new JwtTokenizer(
            secret: 'test-secret-key-that-is-long-enough-for-hmac',
            clock: $pastClock,
        );
        $token = $pastGenerator->generate(TokenPayload::fromArray(['user_id' => '123']), 60);

        $futureClock = new Clock(stub: new \DateTimeImmutable('2024-06-01 00:00:00', new \DateTimeZone('UTC')));
        $futureGenerator = new JwtTokenizer(
            secret: 'test-secret-key-that-is-long-enough-for-hmac',
            clock: $futureClock,
        );

        $i->expectThrowable(\RuntimeException::class, static function () use ($futureGenerator, $token): void {
            $futureGenerator->verify($token);
        });
    }

    public function testTamperedTokenThrowsException(UnitTester $i): void
    {
        $token = $this->generator->generate(TokenPayload::fromArray(['user_id' => '123']), 3600);
        $tamperedToken = $token . 'tampered';

        $i->expectThrowable(\RuntimeException::class, function () use ($tamperedToken): void {
            $this->generator->verify($tamperedToken);
        });
    }
}
