<?php

namespace Tests\Api;

use Tests\Support\ApiTester;

class NotFoundCest
{
    public function testTry(ApiTester $I): void
    {
        $I->haveHttpHeader('Accept', 'application/json');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGet('/not-found');
        $I->seeResponseCodeIs(404);
        $I->seeResponseContainsJson(['message' => 'Not found.']);
    }
}
