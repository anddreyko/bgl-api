<?php

namespace Tests\Acceptance;

use Tests\Support\AcceptanceTester;

class HelloWorldCest
{
    public function testTry(AcceptanceTester $I): void
    {
        $I->amOnPage('/v1/hello-world');
        $I->seeResponseCodeIs(200);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->see('{"data":"Hello world!","result":true}');
    }
}
