<?php

namespace Tests\Api\V1\Auth;

use App\Infrastructure\Http\Enums\HttpCodesEnum;
use Tests\Support\ApiTester;
use Tests\Support\Fixtures\ExistedUserFixture;
use Tests\Support\Fixtures\TwoSessionsUserFixture;
use Tests\Support\Helper\FixtureHelper;

use function PHPUnit\Framework\assertNotEquals;

/**
 * @covers \App\Presentation\Web\V1\Auth\SignOutAction
 */
class SignOutCest
{
    use FixtureHelper;

    public function testSuccess(ApiTester $I): void
    {
        $this->loadFixture(ExistedUserFixture::class);

        $I->haveHttpHeader('Accept', 'application/json');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->haveHttpHeader('Authorization', 'Bearer ' . ExistedUserFixture::$token);
        $I->sendPost('/v1/auth/sign-out');

        $I->seeResponseCodeIs(HttpCodesEnum::Success->value);
        $I->seeResponseContainsJson(['data' => 'sign out', 'result' => true]);

        $I->sendPost('/v1/auth/sign-out');
        $I->seeResponseCodeIs(HttpCodesEnum::BadRequest->value);
        $I->seeResponseContainsJson(['message' => 'Incorrect token.', 'result' => false]);
    }

    public function testTwoSessions(ApiTester $I): void
    {
        $this->loadFixture(TwoSessionsUserFixture::class);

        assertNotEquals(TwoSessionsUserFixture::$token1, TwoSessionsUserFixture::$token2);

        $I->haveHttpHeader('Accept', 'application/json');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->haveHttpHeader('Authorization', 'Bearer ' . TwoSessionsUserFixture::$token1);
        $I->sendPost('/v1/auth/sign-out');

        $I->seeResponseCodeIs(HttpCodesEnum::Success->value);
        $I->seeResponseContainsJson(['data' => 'sign out', 'result' => true]);

        $I->unsetHttpHeader('Authorization');
        $I->haveHttpHeader('Authorization', 'Bearer ' . TwoSessionsUserFixture::$token2);
        $I->sendPost('/v1/auth/sign-out');

        $I->seeResponseCodeIs(HttpCodesEnum::Success->value);
        $I->seeResponseContainsJson(['data' => 'sign out', 'result' => true]);

        $I->sendPost('/v1/auth/sign-out');
        $I->seeResponseCodeIs(HttpCodesEnum::BadRequest->value);
        $I->seeResponseContainsJson(['message' => 'Incorrect token.', 'result' => false]);
    }

    public function testNotAuth(ApiTester $I): void
    {
        $this->loadFixture(ExistedUserFixture::class);

        $I->haveHttpHeader('Accept', 'application/json');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPost('/v1/auth/sign-out');

        $I->seeResponseCodeIs(HttpCodesEnum::Unauthorized->value);
    }
}
