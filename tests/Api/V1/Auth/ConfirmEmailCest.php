<?php

namespace Tests\Api\V1\Auth;

use App\Core\Http\Enums\HttpCodesEnum;
use Tests\Support\ApiTester;
use Tests\Support\Fixtures\ExpiredTokenFixture;
use Tests\Support\Fixtures\NotActiveUserFixture;
use Tests\Support\Helper\FixtureHelper;

/**
 * @covers \Actions\V1\Auth\SignUpAction
 */
class ConfirmEmailCest
{
    use FixtureHelper;

    public function testSuccess(ApiTester $I): void
    {
        $this->loadFixture(NotActiveUserFixture::class);

        $I->haveHttpHeader('Accept', 'application/json');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGet('/v1/auth/confirm-email', ['token' => NotActiveUserFixture::UUID]);
        $I->seeResponseCodeIs(HttpCodesEnum::Success->value);
        $I->seeResponseContainsJson(['data' => 'Specified email is confirmed', 'result' => true]);
    }

    public function testNotFound(ApiTester $I): void
    {
        $I->haveHttpHeader('Accept', 'application/json');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGet('/v1/auth/confirm-email', ['token' => '33333333-3333-3333-3333-333333333333']);
        $I->seeResponseCodeIs(HttpCodesEnum::BadRequest->value);
        $I->seeResponseContainsJson(['message' => 'Incorrect token.']);
    }

    public function testUnspecifiedParameters(ApiTester $I): void
    {
        $I->haveHttpHeader('Accept', 'application/json');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGet('/v1/auth/confirm-email');
        $I->seeResponseCodeIs(HttpCodesEnum::UnprocessableEntity->value);
        $I->seeResponseContainsJson(['message' => HttpCodesEnum::UnprocessableEntity->label()]);
    }

    public function testExpiredToken(ApiTester $I): void
    {
        $this->loadFixture(ExpiredTokenFixture::class);

        $I->haveHttpHeader('Accept', 'application/json');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGet('/v1/auth/confirm-email', ['token' => ExpiredTokenFixture::UUID]);
        $I->seeResponseCodeIs(HttpCodesEnum::Conflict->value);
        $I->seeResponseContainsJson(['message' => 'This token has been expired.']);
    }
}
