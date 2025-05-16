<?php

namespace Tests\Api\V1\User;

use App\Infrastructure\Http\Enums\HttpCodesEnum;
use Tests\Support\ApiTester;
use Tests\Support\Fixtures\ExistedUserFixture;
use Tests\Support\Fixtures\ExpiredTokenUserFixture;
use Tests\Support\Fixtures\OtherUserFixture;
use Tests\Support\Helper\FixtureHelper;

/**
 * @covers \App\Presentation\Web\V1\User\InfoAction
 */
class InfoCest
{
    use FixtureHelper;

    public function testSuccess(ApiTester $I): void
    {
        $this->loadFixture(ExistedUserFixture::class);

        $I->haveHttpHeader('Accept', 'application/json');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->haveHttpHeader('Authorization', 'Bearer ' . ExistedUserFixture::$token->getValue());
        $I->sendGet('/v1/user/' . ExistedUserFixture::UUID);

        $I->seeResponseCodeIs(HttpCodesEnum::Success->value);
        $I->seeResponseContainsJson(['data' => [], 'result' => true]);
        $I->seeResponseContains('"id":"' . ExistedUserFixture::UUID . '"');
        $I->seeResponseContains('"email":"' . ExistedUserFixture::EMAIL . '"');
        $I->seeResponseContains('"is_active":true');
        $I->dontSeeResponseContains(ExistedUserFixture::HASH);
    }

    public function testNotExistUser(ApiTester $I): void
    {
        $this->loadFixture(ExistedUserFixture::class);

        $I->haveHttpHeader('Accept', 'application/json');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->haveHttpHeader('Authorization', 'Bearer ' . ExistedUserFixture::$token->getValue());
        $I->sendGet('/v1/user/33333333-3333-3333-3333-333333333333');

        $I->seeResponseCodeIs(HttpCodesEnum::NotFound->value);
        $I->seeResponseContainsJson(
            ['message' => 'User #33333333-3333-3333-3333-333333333333 not found.', 'result' => false, 'exception' => []]
        );
    }

    public function testUnspecifiedUser(ApiTester $I): void
    {
        $this->loadFixture(ExistedUserFixture::class);

        $I->haveHttpHeader('Accept', 'application/json');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->haveHttpHeader('Authorization', 'Bearer ' . ExistedUserFixture::$token->getValue());
        $I->sendGet('/v1/user');

        $I->seeResponseCodeIs(HttpCodesEnum::NotFound->value);
        $I->seeResponseContainsJson(
            ['message' => HttpCodesEnum::NotFound->label(), 'result' => false, 'exception' => []]
        );
    }

    public function testExpiredToken(ApiTester $I): void
    {
        $this->loadFixture(ExpiredTokenUserFixture::class);

        $I->haveHttpHeader('Accept', 'application/json');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->haveHttpHeader('Authorization', 'Bearer ' . ExpiredTokenUserFixture::$token->getValue());
        $I->sendGet('/v1/user/' . ExpiredTokenUserFixture::UUID);

        $I->seeResponseCodeIs(HttpCodesEnum::Conflict->value);
        $I->seeResponseContainsJson(['message' => 'Expired token', 'result' => false, 'exception' => []]);
    }

    public function testUnauthorized(ApiTester $I): void
    {
        $this->loadFixture(ExistedUserFixture::class);

        $I->haveHttpHeader('Accept', 'application/json');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGet('/v1/user/' . ExistedUserFixture::UUID);

        $I->seeResponseCodeIs(HttpCodesEnum::Unauthorized->value);
        $I->seeResponseContainsJson(['message' => 'Unauthorized.', 'result' => false, 'exception' => []]);
    }

    public function testEmptyBearer(ApiTester $I): void
    {
        $this->loadFixture(ExistedUserFixture::class);

        $I->haveHttpHeader('Accept', 'application/json');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->haveHttpHeader('Authorization', 'Bearer  ');
        $I->sendGet('/v1/user/' . ExistedUserFixture::UUID);

        $I->seeResponseCodeIs(HttpCodesEnum::Unauthorized->value);
        $I->seeResponseContainsJson(['message' => 'Unauthorized.', 'result' => false, 'exception' => []]);
    }

    public function testOtherUser(ApiTester $I): void
    {
        $this->loadFixture([ExistedUserFixture::class, OtherUserFixture::class]);

        $I->haveHttpHeader('Accept', 'application/json');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->haveHttpHeader('Authorization', 'Bearer ' . ExistedUserFixture::$token->getValue());
        $I->sendGet('/v1/user/' . OtherUserFixture::UUID);

        $I->seeResponseCodeIs(HttpCodesEnum::Success->value);
        $I->seeResponseContainsJson(['data' => [], 'result' => true]);
        $I->seeResponseContains('"id":"' . OtherUserFixture::UUID . '"');
        $I->seeResponseContains('"email":"' . OtherUserFixture::EMAIL . '"');
        $I->seeResponseContains('"is_active":true');
        $I->dontSeeResponseContains(OtherUserFixture::HASH);
    }
}
