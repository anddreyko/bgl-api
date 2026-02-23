<?php

declare(strict_types=1);

namespace Bgl\Tests\Web;

use Bgl\Tests\Support\Modules\AuthModule;
use Bgl\Tests\Support\WebTester;
use Codeception\Attribute\Group;

final class PlaySessionCest
{
    public function _before(WebTester $i): void
    {
        $i->haveHttpHeader('Content-Type', 'application/json');
    }

    #[Group('smoke')]
    public function testOpenAndCloseSession(WebTester $i, AuthModule $auth): void
    {
        $email = 'plays-' . uniqid() . '@test.local';
        $password = 'SecurePass1!';

        $auth->registerAndLogin($email, $password);

        $i->sendPost('/v1/plays/sessions');
        $i->seeResponseCodeIs(200);
        $i->seeResponseIsJson();
        $i->seeResponseMatchesJsonType([
            'data' => [
                'session_id' => 'string',
            ],
        ]);

        $sessionId = $i->grabDataFromResponseByJsonPath('$.data.session_id')[0];

        $i->sendPatch('/v1/plays/sessions/' . $sessionId);
        $i->seeResponseCodeIs(200);
        $i->seeResponseIsJson();
        $i->seeResponseMatchesJsonType([
            'data' => [
                'session_id' => 'string',
                'started_at' => 'string',
                'finished_at' => 'string',
            ],
        ]);
    }
}
