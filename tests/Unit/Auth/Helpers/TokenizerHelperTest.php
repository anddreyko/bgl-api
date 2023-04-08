<?php

declare(strict_types=1);

namespace Tests\Unit\Auth\Helpers;

use App\Auth\Helpers\TokenizerHelper;
use Codeception\Test\Unit;

/**
 * @covers \App\Auth\Helpers\TokenizerHelper
 */
class TokenizerHelperTest extends Unit
{
    public function testSuccess(): void
    {
        $duration = new \DateInterval('PT1H');
        $tokenizer = new TokenizerHelper($duration);

        $now = new \DateTimeImmutable();
        $token = $tokenizer->generate($now);

        $this->assertEquals($now->add($duration)->getTimestamp(), $token->getExpires()->getTimestamp());
    }
}
