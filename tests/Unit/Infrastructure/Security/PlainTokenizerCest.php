<?php

declare(strict_types=1);

namespace Bgl\Tests\Unit\Infrastructure\Security;

use Bgl\Core\Clock;
use Bgl\Infrastructure\Security\PlainTokenizer;
use Bgl\Tests\Support\UnitTester;
use Codeception\Attribute\Group;

/**
 * @covers \Bgl\Infrastructure\Security\PlainTokenizer
 */
#[Group('core', 'security', 'tokenizer')]
final class PlainTokenizerCest
{
    private PlainTokenizer $generator;
    private Clock $clock;

    public function _before(): void
    {
        $this->clock = new Clock(stub: new \DateTimeImmutable('2024-06-01 12:00:00', new \DateTimeZone('UTC')));
        $this->generator = new PlainTokenizer(clock: $this->clock);
    }

    public function testGenerateAndVerifyRoundtrip(UnitTester $i): void
    {
        $payload = ['user_id' => '456', 'scope' => 'read'];
        $token = $this->generator->generate($payload, 3600);
        $result = $this->generator->verify($token);

        $i->assertArrayHasKey('user_id', $result);
        $i->assertArrayHasKey('scope', $result);
        $i->assertEquals('456', $result['user_id']);
        $i->assertEquals('read', $result['scope']);
    }

    public function testExpiredTokenThrowsException(UnitTester $i): void
    {
        $pastClock = new Clock(stub: new \DateTimeImmutable('2024-01-01 00:00:00', new \DateTimeZone('UTC')));
        $pastGenerator = new PlainTokenizer(clock: $pastClock);
        $token = $pastGenerator->generate(['user_id' => '456'], 60);

        $futureClock = new Clock(stub: new \DateTimeImmutable('2024-06-01 00:00:00', new \DateTimeZone('UTC')));
        $futureGenerator = new PlainTokenizer(clock: $futureClock);

        $i->expectThrowable(\RuntimeException::class, static function () use ($futureGenerator, $token): void {
            $futureGenerator->verify($token);
        });
    }
}
