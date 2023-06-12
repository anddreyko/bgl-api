<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Tokens\Services;

use App\Auth\ValueObjects\WebToken;
use App\Core\Tokens\Services\JsonWebTokenizerService;
use Codeception\Test\Unit;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\Key;

/**
 * @covers JsonWebTokenizerService
 */
final class JsonWebTokenizerServiceTest extends Unit
{
    private const CORRECT_TOKEN = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJ1c2VyIjoxMjMsImlhdCI6MTY3MjUzMTIwMCwibmJmIjoxNjcyNTMxMjAwLCJleHAiOjQ4MjgyMDQ4MDB9.2kNEeol55xXMHUO-jy072N-VQVfTIt_q2mlVpjESg-Q0ZbKgsbpn02sBGaVjIsjGGMutwaXG-Jwsq6nsC7Necw';
    private const CORRECT_PAYLOAD = ['user' => 123];
    private const INTERVAL = '+100 years';

    private JsonWebTokenizerService $service;
    private \DateTimeImmutable $dateTime;

    protected function setUp(): void
    {
        $this->service = new JsonWebTokenizerService(new Key('some-key', 'HS512'));
        $this->dateTime = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2023-01-01 00:00:00');

        parent::setUp();
    }

    public function testEncode(): void
    {
        $this->assertEquals(
            self::CORRECT_TOKEN,
            $this->service
                ->encode(payload: self::CORRECT_PAYLOAD, expire: self::INTERVAL, issuedAt: $this->dateTime)
                ->getValue()
        );
    }

    public function testDecode(): void
    {
        $this->assertEquals(
            self::CORRECT_PAYLOAD + [
                'iat' => $this->dateTime->getTimestamp(),
                'nbf' => $this->dateTime->getTimestamp(),
                'exp' => $this->dateTime->modify(self::INTERVAL)->getTimestamp(),
            ],
            $this->service->decode(new WebToken(self::CORRECT_TOKEN))
        );
    }

    public function testExpired(): void
    {
        $this->expectException(ExpiredException::class);
        $this->service->decode(new WebToken($this->service->encode(payload: [], expire: '-1 hour')->getValue()));
    }
}
