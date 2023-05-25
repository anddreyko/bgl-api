<?php

namespace Tests\Api\V1\Auth;

use Tests\Support\ApiTester;
use Tests\Support\Fixtures\ExistedUserFixture;
use Tests\Support\Helper\FixtureHelper;
use Tests\Support\Helper\MailerHelper;

/**
 * @covers \Actions\V1\Auth\SignInAction
 */
class SignInCest
{
    use FixtureHelper;
    use MailerHelper;

    public function testSuccess(ApiTester $I): void
    {
        $this->loadFixture(ExistedUserFixture::class);

        $I->haveHttpHeader('Accept', 'application/json');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGet(
            '/v1/auth/login-by-email',
            ['password' => ExistedUserFixture::PASS, 'email' => ExistedUserFixture::EMAIL]
        );
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(
            ['data' => ['token_access' => 'token-access', 'token_update' => 'token-update'], 'result' => true]
        );
    }

    public function testNotExistUser(ApiTester $I): void
    {
        $I->haveHttpHeader('Accept', 'application/json');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGet(
            '/v1/auth/login-by-email',
            ['password' => 'pass', 'email' => 'not-existed-user@app.test']
        );
        $I->seeResponseCodeIs(500);
        $I->seeResponseContainsJson(
            ['message' => 'Unexpected error']
        );
    }

    public function testUnspecifiedUser(ApiTester $I): void
    {
        $I->haveHttpHeader('Accept', 'application/json');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGet(
            '/v1/auth/login-by-email',
            ['password' => ExistedUserFixture::PASS]
        );
        $I->seeResponseCodeIs(500);
        $I->seeResponseContainsJson(
            ['message' => 'Expected a non-empty value. Got: ""']
        );
    }

    public function testUnspecifiedPassword(ApiTester $I): void
    {
        $this->loadFixture(ExistedUserFixture::class);

        $I->haveHttpHeader('Accept', 'application/json');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGet(
            '/v1/auth/login-by-email',
            ['email' => ExistedUserFixture::EMAIL]
        );
        $I->seeResponseCodeIs(500);
        $I->seeResponseContainsJson(
            ['message' => 'Expected a non-empty value. Got: ""']
        );
    }
}
