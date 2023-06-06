<?php

namespace Tests\Acceptance;

use Tests\Support\AcceptanceTester;

class HelloWorldCest
{
    public function testSuccess(AcceptanceTester $I): void
    {
        $I->amOnPage('/v1/hello-world');

        $I->seeResponseCodeIs(200);
        $I->see('{"data":"Hello world!","result":true}');
    }

    public function testAcceptLanguage(AcceptanceTester $I): void
    {
        $I->haveHttpHeader('Accept-Language', 'ru, en');

        $I->amOnPage('/v1/hello-world');

        $I->seeResponseCodeIs(200);
        // PhpBrowser does not support regular browsing of Cyrillic characters.
        $I->see('{"data":"\u041f\u0440\u0438\u0432\u0435\u0442, \u043c\u0438\u0440!","result":true}');
    }
}
