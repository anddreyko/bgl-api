<?php

namespace Tests\Api\V1\Auth;

use App\Infrastructure\Http\Enums\HttpCodesEnum;
use Tests\Support\ApiTester;
use Tests\Support\Fixtures\ExistedUserFixture;
use Tests\Support\Helper\FixtureHelper;
use Tests\Support\Helper\MailerHelper;

/**
 * @covers \App\Presentation\Web\V1\Auth\SignInAction
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
        $I->sendPost(
            '/v1/auth/sign-in-by-email',
            ['password' => ExistedUserFixture::PASS, 'email' => ExistedUserFixture::EMAIL]
        );
        $I->seeResponseCodeIs(HttpCodesEnum::Success->value);
        $I->seeResponseContainsJson(['data' => [], 'result' => true]);
        $I->seeResponseContains('token_access');
        $I->seeResponseContains('.');
    }

    public function testNotExistUser(ApiTester $I): void
    {
        $I->haveHttpHeader('Accept', 'application/json');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPost(
            '/v1/auth/sign-in-by-email',
            ['password' => 'pass', 'email' => 'not-existed-user@app.test']
        );
        $I->seeResponseCodeIs(HttpCodesEnum::BadRequest->value);
        $I->seeResponseContainsJson(
            ['message' => 'Incorrect email or password.']
        );
    }

    public function testUnspecifiedUser(ApiTester $I): void
    {
        $I->haveHttpHeader('Accept', 'application/json');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPost(
            '/v1/auth/sign-in-by-email',
            ['password' => ExistedUserFixture::PASS]
        );
        $I->seeResponseCodeIs(HttpCodesEnum::BadRequest->value);
        $I->seeResponseContainsJson(
            ['message' => 'Expected a non-empty value. Got: ""']
        );
    }

    public function testUnspecifiedPassword(ApiTester $I): void
    {
        $this->loadFixture(ExistedUserFixture::class);

        $I->haveHttpHeader('Accept', 'application/json');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPost(
            '/v1/auth/sign-in-by-email',
            ['email' => ExistedUserFixture::EMAIL]
        );
        $I->seeResponseCodeIs(HttpCodesEnum::BadRequest->value);
        $I->seeResponseContainsJson(
            ['message' => 'Expected a non-empty value. Got: ""']
        );
    }
}
