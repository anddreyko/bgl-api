<?php

namespace Tests\Api\V1\Auth;

use App\Core\Http\Enums\HttpCodesEnum;
use Tests\Support\ApiTester;
use Tests\Support\Fixtures\ExistedUserFixture;
use Tests\Support\Fixtures\NotActiveUserFixture;
use Tests\Support\Helper\FixtureHelper;
use Tests\Support\Helper\MailerHelper;

use function PHPUnit\Framework\assertTrue;

/**
 * @covers \Actions\V1\Auth\SignUpAction
 */
class SignUpCest
{
    use FixtureHelper;
    use MailerHelper;

    private const URI = '/v1/auth/sign-up-by-email';

    public function testSuccess(ApiTester $I): void
    {
        $this->loadFixture();
        $this->cleanMails();

        $I->haveHttpHeader('Accept', 'application/json');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGet(self::URI, ['password' => 'password', 'email' => 'new-user@app.test']);
        $I->seeResponseCodeIs(HttpCodesEnum::Success->value);
        $I->seeResponseContainsJson(['data' => 'Confirm the specified email', 'result' => true]);

        assertTrue($this->checkMails('new-user@app.test'));
    }

    public function testExpireToken(ApiTester $I): void
    {
        $this->loadFixture(NotActiveUserFixture::class);
        $this->cleanMails();

        $I->haveHttpHeader('Accept', 'application/json');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGet(self::URI, ['password' => 'password', 'email' => NotActiveUserFixture::EMAIL]);
        $I->seeResponseCodeIs(HttpCodesEnum::Success->value);
        $I->seeResponseContainsJson(['data' => 'Confirm the specified email', 'result' => true]);

        assertTrue($this->checkMails(NotActiveUserFixture::EMAIL));
    }

    public function testUserAlreadyExist(ApiTester $I): void
    {
        $this->loadFixture(ExistedUserFixture::class);

        $I->haveHttpHeader('Accept', 'application/json');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGet(self::URI, ['password' => 'password', 'email' => ExistedUserFixture::EMAIL]);
        $I->seeResponseCodeIs(HttpCodesEnum::Conflict->value);
        $I->seeResponseContainsJson(['message' => 'User with this email has been already exist.']);
    }

    public function testEmptyPassword(ApiTester $I): void
    {
        $I->haveHttpHeader('Accept', 'application/json');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGet(self::URI, ['password' => '', 'email' => 'empty-password@app.test']);
        $I->seeResponseCodeIs(HttpCodesEnum::BadRequest->value);
        $I->seeResponseContainsJson(['message' => 'Expected a non-empty value. Got: ""']);
    }

    public function testUnspecifiedPassword(ApiTester $I): void
    {
        $I->haveHttpHeader('Accept', 'application/json');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGet(self::URI, ['email' => 'empty-password@app.test']);
        $I->seeResponseCodeIs(HttpCodesEnum::BadRequest->value);
        $I->seeResponseContainsJson(['message' => 'Expected a non-empty value. Got: ""']);
    }

    public function testEmptyEmail(ApiTester $I): void
    {
        $I->haveHttpHeader('Accept', 'application/json');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGet(self::URI, ['password' => 'password', 'email' => '']);
        $I->seeResponseCodeIs(HttpCodesEnum::BadRequest->value);
        $I->seeResponseContainsJson(['message' => 'Expected a non-empty value. Got: ""']);
    }

    public function testUnspecifiedEmail(ApiTester $I): void
    {
        $I->haveHttpHeader('Accept', 'application/json');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGet(self::URI, ['password' => 'password']);
        $I->seeResponseCodeIs(HttpCodesEnum::BadRequest->value);
        $I->seeResponseContainsJson(['message' => 'Expected a non-empty value. Got: ""']);
    }

    public function testUnspecifiedParams(ApiTester $I): void
    {
        $I->haveHttpHeader('Accept', 'application/json');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGet(self::URI);
        $I->seeResponseCodeIs(HttpCodesEnum::BadRequest->value);
        $I->seeResponseContainsJson(['message' => 'Expected a non-empty value. Got: ""']);
    }

    public function testIncorrectEmail(ApiTester $I): void
    {
        $I->haveHttpHeader('Accept', 'application/json');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGet(self::URI, ['password' => 'password', 'email' => 'incorrect-email']);
        $I->seeResponseCodeIs(HttpCodesEnum::BadRequest->value);
        $I->seeResponseContainsJson(
            ['message' => 'Expected a value to be a valid e-mail address. Got: "incorrect-email"']
        );
    }
}
