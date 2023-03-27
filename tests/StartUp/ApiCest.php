<?php

namespace Tests\StartUp;

use Tests\Support\AcceptanceTester;

class ApiCest
{
    public function tryApi(AcceptanceTester $I): void
    {
        $I->amOnPage('/');
        $I->seeResponseCodeIs(200);
    }
}
