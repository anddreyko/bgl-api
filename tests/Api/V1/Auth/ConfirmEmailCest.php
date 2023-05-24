<?php

namespace Tests\Api\V1\Auth;

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
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['data' => 'Specified email is confirmed', 'result' => true]);
    }

    public function testNotFound(ApiTester $I): void
    {
        $I->haveHttpHeader('Accept', 'application/json');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGet('/v1/auth/confirm-email', ['token' => '33333333-3333-3333-3333-333333333333']);
        $I->seeResponseCodeIs(500);
        $I->seeResponseContainsJson(['message' => 'Incorrect token.']);
    }

    public function testUnspecifiedFound(ApiTester $I): void
    {
        $I->haveHttpHeader('Accept', 'application/json');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGet('/v1/auth/confirm-email');
        $I->seeResponseCodeIs(500);
        $I->seeResponseContainsJson(['message' => 'Incorrect token.']);
    }

    public function testExpiredToken(ApiTester $I): void
    {
        $this->loadFixture(ExpiredTokenFixture::class);

        $I->haveHttpHeader('Accept', 'application/json');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGet('/v1/auth/confirm-email', ['token' => ExpiredTokenFixture::UUID]);
        $I->seeResponseCodeIs(500);
        $I->seeResponseContainsJson(['message' => 'This token has been expired.']);
    }
}
