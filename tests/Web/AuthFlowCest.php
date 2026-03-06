<?php

declare(strict_types=1);

namespace Bgl\Tests\Web;

use Bgl\Tests\Support\WebTester;
use Codeception\Attribute\Group;

final class AuthFlowCest
{
    public function _before(WebTester $i): void
    {
        $i->haveHttpHeader('Content-Type', 'application/json');
    }

    #[Group('smoke')]
    public function testRegistrationLoginAndSignOut(WebTester $i): void
    {
        $email = 'auth-flow-' . uniqid() . '@test.local';
        $password = 'SecurePass1!';

        $i->sendPost('/v1/auth/sign-up', [
            'email' => $email,
            'password' => $password,
        ]);
        $i->seeResponseCodeIs(200);
        $i->seeResponseIsJson();

        $i->updateInDatabase('auth_user', ['status' => 'active'], ['email' => $email]);

        $i->sendPost('/v1/auth/sign-in', [
            'email' => $email,
            'password' => $password,
        ]);
        $i->seeResponseCodeIs(200);
        $i->seeResponseIsJson();
        $i->seeResponseMatchesJsonType([
            'data' => [
                'access_token' => 'string',
                'refresh_token' => 'string',
            ],
        ]);

        $accessToken = $i->grabDataFromResponseByJsonPath('$.data.access_token')[0];

        $i->haveHttpHeader('Authorization', 'Bearer ' . $accessToken);
        $i->sendPost('/v1/auth/sign-out');
        $i->seeResponseCodeIs(200);

        $i->sendPost('/v1/auth/sign-out');
        $i->seeResponseCodeIs(401);
    }

    #[Group('smoke')]
    public function testTokenRefresh(WebTester $i): void
    {
        $email = 'refresh-' . uniqid() . '@test.local';
        $password = 'SecurePass1!';

        $i->sendPost('/v1/auth/sign-up', [
            'email' => $email,
            'password' => $password,
        ]);
        $i->seeResponseCodeIs(200);

        $i->updateInDatabase('auth_user', ['status' => 'active'], ['email' => $email]);

        $i->sendPost('/v1/auth/sign-in', [
            'email' => $email,
            'password' => $password,
        ]);
        $i->seeResponseCodeIs(200);

        $refreshToken = $i->grabDataFromResponseByJsonPath('$.data.refresh_token')[0];

        $i->sendPost('/v1/auth/refresh', [
            'refresh_token' => $refreshToken,
        ]);
        $i->seeResponseCodeIs(200);
        $i->seeResponseIsJson();
        $i->seeResponseMatchesJsonType([
            'data' => [
                'access_token' => 'string',
                'refresh_token' => 'string',
            ],
        ]);
    }

    #[Group('smoke')]
    public function testUserInfo(WebTester $i): void
    {
        $email = 'userinfo-' . uniqid() . '@test.local';
        $password = 'SecurePass1!';

        $i->sendPost('/v1/auth/sign-up', [
            'email' => $email,
            'password' => $password,
        ]);
        $i->seeResponseCodeIs(200);

        $i->updateInDatabase('auth_user', ['status' => 'active'], ['email' => $email]);

        $i->sendPost('/v1/auth/sign-in', [
            'email' => $email,
            'password' => $password,
        ]);
        $i->seeResponseCodeIs(200);

        $accessToken = $i->grabDataFromResponseByJsonPath('$.data.access_token')[0];
        $userId = $i->grabFromDatabase('auth_user', 'id', ['email' => $email]);

        $i->haveHttpHeader('Authorization', 'Bearer ' . $accessToken);
        $i->sendGet('/v1/user/' . $userId);
        $i->seeResponseCodeIs(200);
        $i->seeResponseIsJson();
        $i->seeResponseMatchesJsonType([
            'data' => [
                'id' => 'string',
                'email' => 'string',
                'is_active' => 'boolean',
                'created_at' => 'string',
            ],
        ]);
        $i->seeResponseContainsJson(['data' => ['email' => $email, 'is_active' => true]]);
    }
}
