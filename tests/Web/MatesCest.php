<?php

declare(strict_types=1);

namespace Bgl\Tests\Web;

use Bgl\Tests\Support\Modules\AuthModule;
use Bgl\Tests\Support\WebTester;
use Codeception\Attribute\Group;

final class MatesCest
{
    public function _before(WebTester $i): void
    {
        $i->haveHttpHeader('Content-Type', 'application/json');
    }

    #[Group('smoke')]
    public function testCreateAndListMates(WebTester $i, AuthModule $auth): void
    {
        $email = 'mates-' . uniqid() . '@test.local';
        $auth->registerAndLogin($email, 'SecurePass1!');

        // Create mate
        $i->sendPost('/v1/mates', ['name' => 'Ivan', 'notes' => 'Likes Carcassonne']);
        $i->seeResponseCodeIs(201);
        $i->seeResponseIsJson();
        $i->seeResponseMatchesJsonType([
            'data' => [
                'id' => 'string',
                'name' => 'string',
                'notes' => 'string',
                'created_at' => 'string',
            ],
        ]);

        $mateId = $i->grabDataFromResponseByJsonPath('$.data.id')[0];

        // List mates
        $i->sendGet('/v1/mates');
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

        // Get mate
        $i->sendGet('/v1/mates/' . $mateId);
        $i->seeResponseCodeIs(200);
        $i->seeResponseMatchesJsonType([
            'data' => [
                'id' => 'string',
                'name' => 'string',
            ],
        ]);

        // Update mate
        $i->sendPut('/v1/mates/' . $mateId, ['name' => 'Ivan Petrov', 'notes' => 'Updated']);
        $i->seeResponseCodeIs(200);
        $i->seeResponseMatchesJsonType([
            'data' => [
                'id' => 'string',
                'name' => 'string',
            ],
        ]);

        // Delete mate
        $i->sendDelete('/v1/mates/' . $mateId);
        $i->seeResponseCodeIs(204);
    }

    public function testCreateMateRequiresAuth(WebTester $i): void
    {
        $i->sendPost('/v1/mates', ['name' => 'Ivan']);
        $i->seeResponseCodeIs(401);
    }

    public function testCreateMateRequiresName(WebTester $i, AuthModule $auth): void
    {
        $email = 'mates-val-' . uniqid() . '@test.local';
        $auth->registerAndLogin($email, 'SecurePass1!');

        $i->sendPost('/v1/mates', []);
        $i->seeResponseCodeIs(422);
    }
}
