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

    public function testOpenSessionWithoutTokenReturns401(WebTester $i): void
    {
        $i->sendPost('/v1/plays/sessions', [
            'players' => [
                ['mate_id' => '00000000-0000-0000-0000-000000000001'],
            ],
        ]);
        $i->seeResponseCodeIs(401);
    }

    public function testOpenSessionWithoutBodyReturns401(WebTester $i): void
    {
        $i->sendPost('/v1/plays/sessions');
        $i->seeResponseCodeIs(401);
    }

    #[Group('smoke')]
    public function testGetSessionReturns200ForOwner(WebTester $i, AuthModule $auth): void
    {
        $email = 'plays-get-' . uniqid() . '@test.local';
        $auth->registerAndLogin($email, 'SecurePass1!');

        $i->sendPost('/v1/plays/sessions');
        $i->seeResponseCodeIs(201);
        $sessionId = $i->grabDataFromResponseByJsonPath('$.data.id')[0];

        $i->sendGet('/v1/plays/sessions/' . $sessionId);
        $i->seeResponseCodeIs(200);
        $i->seeResponseIsJson();
        $i->seeResponseMatchesJsonType([
            'data' => [
                'id' => 'string',
                'author' => [
                    'id' => 'string',
                    'name' => 'string',
                ],
                'status' => 'string',
                'visibility' => 'string',
                'started_at' => 'string',
            ],
        ]);
    }

    public function testGetSessionReturns404ForNonExistent(WebTester $i, AuthModule $auth): void
    {
        $email = 'plays-get-404-' . uniqid() . '@test.local';
        $auth->registerAndLogin($email, 'SecurePass1!');

        $i->sendGet('/v1/plays/sessions/00000000-0000-0000-0000-000000000000');
        $i->seeResponseCodeIs(404);
    }

    #[Group('smoke')]
    public function testOpenAndCloseSession(WebTester $i, AuthModule $auth): void
    {
        $email = 'plays-' . uniqid() . '@test.local';
        $password = 'SecurePass1!';

        $auth->registerAndLogin($email, $password);

        $i->sendPost('/v1/plays/sessions');
        $i->seeResponseCodeIs(201);
        $i->seeResponseIsJson();
        $i->seeResponseMatchesJsonType([
            'data' => [
                'id' => 'string',
            ],
        ]);

        $sessionId = $i->grabDataFromResponseByJsonPath('$.data.id')[0];

        $i->sendPatch('/v1/plays/sessions/' . $sessionId);
        $i->seeResponseCodeIs(200);
        $i->seeResponseIsJson();
        $i->seeResponseMatchesJsonType([
            'data' => [
                'id' => 'string',
                'started_at' => 'string',
                'finished_at' => 'string',
            ],
        ]);
    }

    #[Group('smoke')]
    public function testOpenSessionWithPlayersAndClose(WebTester $i, AuthModule $auth): void
    {
        $email = 'plays-players-' . uniqid('', true) . '@test.local';
        $auth->registerAndLogin($email, 'SecurePass1!');

        // Create two mates
        $i->sendPost('/v1/mates', ['name' => 'Alice']);
        $i->seeResponseCodeIs(201);
        $mate1Id = $i->grabDataFromResponseByJsonPath('$.data.id')[0];

        $i->sendPost('/v1/mates', ['name' => 'Bob']);
        $i->seeResponseCodeIs(201);
        $mate2Id = $i->grabDataFromResponseByJsonPath('$.data.id')[0];

        // Create play session with players
        $i->sendPost('/v1/plays/sessions', [
            'name' => 'Game night',
            'visibility' => 'participants',
            'players' => [
                ['mate_id' => $mate1Id, 'score' => 10, 'is_winner' => true, 'color' => 'red'],
                ['mate_id' => $mate2Id, 'score' => 5, 'is_winner' => false, 'color' => 'blue'],
            ],
        ]);
        $i->seeResponseCodeIs(201);

        $sessionId = $i->grabDataFromResponseByJsonPath('$.data.id')[0];

        // Verify play session persisted in DB
        $i->seeInDatabase('plays_session', [
            'id' => $sessionId,
            'name' => 'Game night',
            'status' => 'draft',
            'visibility' => 'participants',
        ]);

        // Verify players persisted in DB via cascade
        $i->seeNumRecords(2, 'plays_player', ['play_id' => $sessionId]);
        $i->seeInDatabase('plays_player', [
            'play_id' => $sessionId,
            'mate_id' => $mate1Id,
            'score' => 10,
            'is_winner' => true,
            'color' => 'red',
        ]);
        $i->seeInDatabase('plays_player', [
            'play_id' => $sessionId,
            'mate_id' => $mate2Id,
            'score' => 5,
            'is_winner' => false,
            'color' => 'blue',
        ]);

        // Finalize (close) session
        $i->sendPatch('/v1/plays/sessions/' . $sessionId);
        $i->seeResponseCodeIs(200);

        // Verify status changed in DB
        $i->seeInDatabase('plays_session', [
            'id' => $sessionId,
            'status' => 'draft',
        ]);
    }

    #[Group('smoke')]
    public function testListSessionsReturns200(WebTester $i, AuthModule $auth): void
    {
        $email = 'plays-list-' . uniqid() . '@test.local';
        $auth->registerAndLogin($email, 'SecurePass1!');

        // Create a session first
        $i->sendPost('/v1/plays/sessions');
        $i->seeResponseCodeIs(201);

        // List sessions
        $i->sendGet('/v1/plays/sessions');
        $i->seeResponseCodeIs(200);
        $i->seeResponseIsJson();
        $i->seeResponseMatchesJsonType([
            'data' => [
                'items' => 'array',
                'total' => 'integer',
                'page' => 'integer',
                'size' => 'integer',
            ],
        ]);
    }

    public function testListSessionsWithoutTokenReturns401(WebTester $i): void
    {
        $i->sendGet('/v1/plays/sessions');
        $i->seeResponseCodeIs(401);
    }
}
