<?php

declare(strict_types=1);

namespace Bgl\Tests\Web;

use Bgl\Core\ValueObjects\DateTime;
use Bgl\Tests\Support\WebTester;
final class GamesCest
{
    public function _before(WebTester $i): void
    {
        $i->haveHttpHeader('Content-Type', 'application/json');
    }

    public function testSearchRequiresQuery(WebTester $i): void
    {
        $i->sendGet('/v1/games/search');
        $i->seeResponseCodeIs(422);
    }

    public function testSearchQueryTooShort(WebTester $i): void
    {
        $i->sendGet('/v1/games/search?q=ab');
        $i->seeResponseCodeIs(422);
    }

    public function testGetGameNotFoundReturns404(WebTester $i): void
    {
        $i->sendGet('/v1/games/00000000-0000-0000-0000-000000000000');
        $i->seeResponseCodeIs(404);
    }

    public function testGetGameReturns200(WebTester $i): void
    {
        $gameId = '10000000-0000-0000-0000-000000000001';
        $now = new DateTime('now')->getFormattedValue('Y-m-d H:i:s');
        $i->haveInDatabase('games_game', [
            'id' => $gameId,
            'bgg_id' => 999999,
            'name' => 'Test Game',
            'year_published' => 2024,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $i->sendGet('/v1/games/' . $gameId);
        $i->seeResponseCodeIs(200);
        $i->seeResponseIsJson();
        $i->seeResponseMatchesJsonType([
            'data' => [
                'id' => 'string',
                'bgg_id' => 'integer',
                'name' => 'string',
                'year_published' => 'integer|null',
            ],
        ]);
    }
}
