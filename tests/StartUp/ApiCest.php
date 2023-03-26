<?php

namespace Tests\StartUp;

use Tests\Support\AcceptanceTester;

class ApiCest
{
    public function tryApi(AcceptanceTester $I): void
    {
        $I->amOnPage('/');
        $I->seeResponseCodeIs(200);
        $I->haveHttpHeader('Content-Type', 'application/json');
    }
}
