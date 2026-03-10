<?php

declare(strict_types=1);

namespace Bgl\Tests\Web;

use Bgl\Tests\Support\WebTester;
use Codeception\Attribute\Group;

#[Group('user', 'profile')]
final class UserProfileCest
{
    public function _before(WebTester $i): void
    {
        $i->haveHttpHeader('Content-Type', 'application/json');
    }

    public function testUpdateUserWithoutTokenReturns401(WebTester $i): void
    {
        $i->sendPatch('/v1/user/00000000-0000-4000-8000-000000000001', [
            'name' => 'NewName',
        ]);
        $i->seeResponseCodeIs(401);
    }

    #[Group('smoke')]
    public function testUpdateUserNameSuccessfully(WebTester $i): void
    {
        $email = 'update-profile-' . uniqid() . '@test.local';
        $password = 'SecurePass1!';

        $i->sendPost('/v1/auth/sign-up', [
            'email' => $email,
            'password' => $password,
        ]);
        $i->seeResponseCodeIs(201);

        $i->updateInDatabase('auth_user', ['status' => 'active'], ['email' => $email]);

        $i->sendPost('/v1/auth/sign-in', [
            'email' => $email,
            'password' => $password,
        ]);
        $i->seeResponseCodeIs(200);

        $accessToken = $i->grabDataFromResponseByJsonPath('$.data.access_token')[0];
        $userId = $i->grabFromDatabase('auth_user', 'id', ['email' => $email]);

        $i->haveHttpHeader('Authorization', 'Bearer ' . $accessToken);
        $i->sendPatch('/v1/user/' . $userId, [
            'name' => 'UpdatedName',
        ]);
        $i->seeResponseCodeIs(200);
        $i->seeResponseIsJson();
        $i->seeResponseMatchesJsonType([
            'data' => [
                'id' => 'string',
                'email' => 'string',
                'name' => 'string',
                'is_active' => 'boolean',
                'created_at' => 'string',
            ],
        ]);
        $i->seeResponseContainsJson(['data' => ['name' => 'UpdatedName']]);
    }
}
