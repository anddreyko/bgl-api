<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Tokens\Services;

use App\Infrastructure\Tokens\Tokenizer;
use Codeception\Test\Unit;

/**
 * @covers \App\Infrastructure\Tokens\Tokenizer
 */
class TokenizerServiceTest extends Unit
{
    public function testGenerate(): void
    {
        $duration = new \DateInterval('PT1H');
        $tokenizer = new Tokenizer($duration);

        $now = new \DateTimeImmutable();
        $token = $tokenizer->generate($now);

        $this->assertEquals($now->add($duration)->getTimestamp(), $token->getExpires()->getTimestamp());
    }
}
