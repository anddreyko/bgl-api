<?php

namespace Tests\Api\V1\Auth;

use Tests\Support\ApiTester;
use Tests\Support\Fixtures\ExistedUserFixture;
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

    public function testSuccess(ApiTester $I): void
    {
        $this->loadFixture();
        $this->cleanMails();

        $I->haveHttpHeader('Accept', 'application/json');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGet('/v1/auth/register-by-email', ['password' => 'password', 'email' => 'new-user@app.test']);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['data' => 'Confirm the specified email', 'result' => true]);

        assertTrue($this->checkMails('new-user@app.test'));
    }

    public function testUserAlreadyExist(ApiTester $I): void
    {
        $this->loadFixture(ExistedUserFixture::class);

        $I->haveHttpHeader('Accept', 'application/json');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGet('/v1/auth/register-by-email', ['password' => 'password', 'email' => ExistedUserFixture::EMAIL]);
        $I->seeResponseCodeIs(500);
        $I->seeResponseContainsJson(['message' => 'User with this email has been already exist.']);
    }

    public function testEmptyPassword(ApiTester $I): void
    {
        $I->haveHttpHeader('Accept', 'application/json');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGet('/v1/auth/register-by-email', ['password' => '', 'email' => 'empty-password@app.test']);
        $I->seeResponseCodeIs(500);
        $I->seeResponseContainsJson(['message' => 'Expected a non-empty value. Got: ""']);
    }

    public function testUnspecifiedPassword(ApiTester $I): void
    {
        $I->haveHttpHeader('Accept', 'application/json');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGet('/v1/auth/register-by-email', ['email' => 'empty-password@app.test']);
        $I->seeResponseCodeIs(500);
        $I->seeResponseContainsJson(['message' => 'Expected a non-empty value. Got: ""']);
    }

    public function testEmptyEmail(ApiTester $I): void
    {
        $I->haveHttpHeader('Accept', 'application/json');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGet('/v1/auth/register-by-email', ['password' => 'password', 'email' => '']);
        $I->seeResponseCodeIs(500);
        $I->seeResponseContainsJson(['message' => 'Expected a non-empty value. Got: ""']);
    }

    public function testUnspecifiedEmail(ApiTester $I): void
    {
        $I->haveHttpHeader('Accept', 'application/json');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGet('/v1/auth/register-by-email', ['password' => 'password']);
        $I->seeResponseCodeIs(500);
        $I->seeResponseContainsJson(['message' => 'Expected a non-empty value. Got: ""']);
    }

    public function testUnspecifiedParams(ApiTester $I): void
    {
        $I->haveHttpHeader('Accept', 'application/json');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGet('/v1/auth/register-by-email');
        $I->seeResponseCodeIs(500);
        $I->seeResponseContainsJson(['message' => 'Expected a non-empty value. Got: ""']);
    }

    public function testIncorrectEmail(ApiTester $I): void
    {
        $I->haveHttpHeader('Accept', 'application/json');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGet('/v1/auth/register-by-email', ['password' => 'password', 'email' => 'incorrect-email']);
        $I->seeResponseCodeIs(500);
        $I->seeResponseContainsJson(
            ['message' => 'Expected a value to be a valid e-mail address. Got: "incorrect-email"']
        );
    }
}
