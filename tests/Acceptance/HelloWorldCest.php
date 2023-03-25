<?php

namespace Tests\Acceptance;

use Tests\Support\AcceptanceTester;

class HelloWorldCest
{
    public function testTry(AcceptanceTester $I): void
    {
        $I->amOnPage('/');
        $I->seeResponseCodeIs(200);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->see('{"content":"Hello world!"}');
    }
}
