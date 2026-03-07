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

    public function testSearchWithoutQueryReturnsAll(WebTester $i): void
    {
        $i->sendGet('/v1/games/search');
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

    public function testSearchGamesBggReturnsResults(WebTester $i): void
    {
        $i->sendGet('/v1/games/search?q=catan');
        $i->seeResponseCodeIs(200);
        $i->seeResponseIsJson();
        $i->seeResponseContainsJson(['data' => ['total' => 2]]);
        $i->seeResponseMatchesJsonType([
            'data' => [
                'items' => 'array',
                'total' => 'integer',
                'page' => 'integer',
                'size' => 'integer',
            ],
        ]);
    }

    public function testSearchGamesBggReturnsEmpty(WebTester $i): void
    {
        $i->sendGet('/v1/games/search?q=zzz_nonexistent');
        $i->seeResponseCodeIs(200);
        $i->seeResponseIsJson();
        $i->seeResponseContainsJson(['data' => ['items' => [], 'total' => 0]]);
    }

    public function testSearchGamesWhenBggUnavailableAndNoLocal(WebTester $i): void
    {
        $i->sendGet('/v1/games/search?q=unavailable_trigger');
        $i->seeResponseCodeIs(500);
    }

    public function testSearchGamesWhenBggUnavailableWithLocalFallback(WebTester $i): void
    {
        $now = new DateTime('now')->getFormattedValue('Y-m-d H:i:s');
        $i->haveInDatabase('games_game', [
            'id' => '10000000-0000-0000-0000-000000000099',
            'bgg_id' => 888888,
            'name' => 'unavailable_trigger game',
            'year_published' => 2020,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $i->sendGet('/v1/games/search?q=unavailable_trigger');
        $i->seeResponseCodeIs(200);
        $i->seeResponseIsJson();
        $i->seeResponseContainsJson(['data' => ['total' => 1]]);
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
