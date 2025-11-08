<?php

namespace Bgl\Tests\Web;

use Bgl\Tests\Support\WebTester;
use Codeception\Attribute\Group;

class AccessCest
{
    #[Group('smoke')]
    public function testSuccess(WebTester $i): void
    {
        $i->sendGet('/');
        $i->seeResponseCodeIs(200);
    }
}
