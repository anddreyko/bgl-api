<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Tokens\Services;

use App\Core\Tokens\Services\TokenizerService;
use Codeception\Test\Unit;

/**
 * @covers \App\Core\Tokens\Services\TokenizerService
 */
class TokenizerServiceTest extends Unit
{
    public function testGenerate(): void
    {
        $duration = new \DateInterval('PT1H');
        $tokenizer = new TokenizerService($duration);

        $now = new \DateTimeImmutable();
        $token = $tokenizer->generate($now);

        $this->assertEquals($now->add($duration)->getTimestamp(), $token->getExpires()->getTimestamp());
    }
}
