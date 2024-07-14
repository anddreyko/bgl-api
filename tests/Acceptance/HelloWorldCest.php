<?php

namespace Tests\Acceptance;

use Tests\Support\ApiTester;

class HelloWorldCest
{
    public function testSuccess(ApiTester $I): void
    {
        $I->sendGet('/v1/hello-world');

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsValidOnJsonSchemaString(
            json_encode([
                "properties" => [
                    "code" => [
                        "type" => ["integer", "null"],
                    ],
                    "data" => [
                        "type" => "string",
                    ],
                    "result" => [
                        "type" => "boolean",
                    ],
                ],
            ], JSON_THROW_ON_ERROR)
        );
    }

    public function testAcceptLanguage(ApiTester $I): void
    {
        $I->haveHttpHeader('Accept-Language', 'ru, en');

        $I->sendGet('/v1/hello-world');

        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['data' => 'Привет, мир!']);
    }
}
